<?php

namespace App\Domain\Run\Jobs;

use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunSettingsKey;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessContractRunArtifactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Run $run,
    ) {
        throw_unless($run->contract, Exception::class, "contract must be assigned to run '$run->id' to extract abi file");
    }

    public function handle(): void
    {
        $tempDir = sys_get_temp_dir()."/runs/{$this->run->id}";
        $tempFileName = "{$tempDir}/artifact.zip";
        mkdir($tempDir, 0777, true);

        $this->streamArtifactToTemp($tempFileName);

        $this->extractZipToTemp($tempFileName, $tempDir);

        $abiFile = $this->findAbiFile($tempDir);
        $codeHash = $this->findCodeHash($tempDir);

        throw_unless($abiFile, Exception::class, 'failed to find abi file');
        throw_unless($codeHash, Exception::class, 'failed to find code hash');

        $this->run->contract->addMedia($abiFile)
            ->toMediaCollection(Contract::MediaLibraryCollectionAbi);

        $this->run->settings()->set(RunSettingsKey::CodeHash->value, $codeHash);
    }

    private function streamArtifactToTemp(string $tempFileName): void
    {
        $media = $this->run->getArtifact() ?? throw new Exception('no artifact found for run '.$this->run->id);

        $content = Storage::disk($media->disk)->get($media->getPath());

        throw_unless($content, Exception::class, 'failed to get content from media file');

        file_put_contents($tempFileName, $content);
    }

    private function extractZipToTemp(string $zipPath, string $tempDir): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            throw new Exception('failed to open zip file');
        }
    }

    private function findAbiFile(string $tempDir): string
    {
        $files = glob($tempDir.'/raw/*/*.abi.json');

        if (count($files) === 0) {
            throw new Exception('no JSON ABI file found in the artifacts');
        }

        return $files[0];
    }

    private function findCodeHash(string $tempDir): string
    {
        $files = glob($tempDir.'/raw/*/*.codehash.txt');

        if (count($files) === 0) {
            throw new Exception('no code hash file found in the artifacts');
        }

        return file_get_contents($files[0]);
    }
}
