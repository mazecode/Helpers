<?php

namespace Siga98\Helpers;

use Exception;

final class FileHelper
{
    /**
     * @param resource|string $resource
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function cleanTemporalData($resource): bool
    {
        try {
            $path = '';

            if (null !== $resource) {
                if (\is_string($resource)) {
                    $path = $resource;
                }

                if (\is_resource($resource)) {
                    $path = self::resourcePath($resource);
                }

                $trying = 10;

                while (\file_exists($path) || 0 === $trying) {
                    // NOTE: Check this later
                    if (\is_resource($resource)) {
                        \fclose($resource);
                    }

                    \unlink($path);
                    --$trying;
                }
            }

            unset($path);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param resource $resource
     *
     * @throws Exception
     *
     * @return string
     */
    public static function resourcePath($resource): string
    {
        try {
            if (\is_resource($resource)) {
                return \stream_get_meta_data($resource)['uri'];
            }

            if (\is_file($resource)) {
                return $resource;
            }

            return $resource;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
