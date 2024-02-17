<?php

declare(strict_types=1);

namespace App\Traits;

use CpChart\Image;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithBrowser
{
    /**
     * Opens the given image in the default web browser.
     *
     * @param Image $image The image to be opened in the browser.
     * @param OutputInterface $output The output interface.
     * @return void
     */
    public function openBrowserImage(Image $image, OutputInterface $output): void
    {
        switch (PHP_OS_FAMILY) {
            case 'Darwin':
                exec("open -a Firefox '{$image->toDataURI()}'");
                break;
            case 'Linux':
                exec("xdg-open '{$image->toDataURI()}'");
                break;
            default:
                $output->writeln($image->toDataURI());
        }
    }
}
