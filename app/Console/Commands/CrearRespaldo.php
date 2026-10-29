<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Copia de seguridad de la base de datos en un archivo .sql.gz (storage/app/private/respaldos).
 * Se programa a diario en routes/console.php. No depende de mysqldump, que no siempre está en el alojamiento.
 * Para restaurar: descomprimir el archivo e importarlo en phpMyAdmin (o «mysql nombre_bd < respaldo.sql»).
 */
class CrearRespaldo extends Command
{
    protected $signature = 'respaldo:crear {--conservar= : Cuántos respaldos guardar (los más antiguos se borran)}';

    protected $description = 'Crea una copia de la base de datos y borra las más antiguas';

    public const CARPETA = 'respaldos';

    /** Tablas temporales del sistema: se guarda su estructura, pero no su contenido. */
    private const SOLO_ESTRUCTURA = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    private const FILAS_POR_INSERT = 200;

    public function handle(): int
    {
        $disco = Storage::disk('local');
        $disco->makeDirectory(self::CARPETA);

        $archivo = self::CARPETA.'/respaldo_'.now()->format('Y-m-d_His').'.sql.gz';
        $gz = gzopen($disco->path($archivo), 'wb6');

        gzwrite($gz, '-- Respaldo de '.config('app.name').' · '.now()->format('d/m/Y H:i')."\n"
            ."SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->tablas() as $tabla) {
            $this->escribirTabla($gz, $tabla);
        }

        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);

        $borrados = $this->borrarAntiguos((int) ($this->option('conservar') ?? config('app.respaldos_conservar')));

        $this->info("Respaldo creado: {$archivo}".($borrados ? " ({$borrados} antiguo(s) borrado(s))" : ''));

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function tablas(): array
    {
        return collect(DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
            ->map(fn ($fila) => array_values((array) $fila)[0])
            ->all();
    }

    /** @param  resource  $gz */
    private function escribirTabla($gz, string $tabla): void
    {
        $crear = array_values((array) DB::selectOne("SHOW CREATE TABLE `{$tabla}`"))[1];
        gzwrite($gz, "DROP TABLE IF EXISTS `{$tabla}`;\n{$crear};\n\n");

        if (in_array($tabla, self::SOLO_ESTRUCTURA, true)) {
            return;
        }

        $pdo = DB::getPdo();
        $lote = [];

        foreach (DB::table($tabla)->cursor() as $fila) {
            $valores = array_map(fn ($valor) => $valor === null ? 'NULL' : $pdo->quote((string) $valor), (array) $fila);
            $lote[] = '('.implode(',', $valores).')';

            if (count($lote) === self::FILAS_POR_INSERT) {
                gzwrite($gz, "INSERT INTO `{$tabla}` VALUES\n".implode(",\n", $lote).";\n");
                $lote = [];
            }
        }

        if ($lote) {
            gzwrite($gz, "INSERT INTO `{$tabla}` VALUES\n".implode(",\n", $lote).";\n");
        }

        gzwrite($gz, "\n");
    }

    private function borrarAntiguos(int $conservar): int
    {
        $disco = Storage::disk('local');
        $sobrantes = collect($disco->files(self::CARPETA))
            ->filter(fn ($ruta) => str_ends_with($ruta, '.sql.gz'))
            ->sortDesc()
            ->slice(max($conservar, 1));

        $disco->delete($sobrantes->all());

        return $sobrantes->count();
    }
}
