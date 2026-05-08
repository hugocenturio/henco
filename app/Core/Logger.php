<?php

namespace App\Core;

/**
 * Tiny PSR-3-flavoured file logger.
 *
 *   Logger::info('User logged in', ['user_id' => 42]);
 *   Logger::error('DB connect failed', ['error' => $e->getMessage()]);
 *
 * - Writes to logs/app-YYYY-MM-DD.log relative to the project root.
 * - Honours the APP_DEBUG env: debug-level messages are dropped when false.
 * - Falls back to error_log() if the logs directory cannot be written to,
 *   so logging is never the cause of a request failure.
 */
class Logger
{
    public const DEBUG    = 'debug';
    public const INFO     = 'info';
    public const NOTICE   = 'notice';
    public const WARNING  = 'warning';
    public const ERROR    = 'error';
    public const CRITICAL = 'critical';

    private const LEVEL_RANK = [
        self::DEBUG    => 0,
        self::INFO     => 1,
        self::NOTICE   => 2,
        self::WARNING  => 3,
        self::ERROR    => 4,
        self::CRITICAL => 5,
    ];

    private static ?string $directory = null;
    private static ?string $minLevel  = null;

    public static function configure(?string $directory = null, ?string $minLevel = null): void
    {
        self::$directory = $directory;
        self::$minLevel  = $minLevel;
    }

    public static function debug(string $msg, array $ctx = []): void    { self::log(self::DEBUG,    $msg, $ctx); }
    public static function info(string $msg, array $ctx = []): void     { self::log(self::INFO,     $msg, $ctx); }
    public static function notice(string $msg, array $ctx = []): void   { self::log(self::NOTICE,   $msg, $ctx); }
    public static function warning(string $msg, array $ctx = []): void  { self::log(self::WARNING,  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void    { self::log(self::ERROR,    $msg, $ctx); }
    public static function critical(string $msg, array $ctx = []): void { self::log(self::CRITICAL, $msg, $ctx); }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (!isset(self::LEVEL_RANK[$level])) {
            $level = self::INFO;
        }
        if (self::LEVEL_RANK[$level] < self::LEVEL_RANK[self::minLevel()]) {
            return;
        }

        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        $file = self::directory() . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log';
        if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
            error_log(rtrim($line));
        }
    }

    private static function directory(): string
    {
        if (self::$directory === null) {
            self::$directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs';
        }
        if (!is_dir(self::$directory)) {
            @mkdir(self::$directory, 0750, true);
        }
        return self::$directory;
    }

    private static function minLevel(): string
    {
        if (self::$minLevel !== null) {
            return self::$minLevel;
        }
        return Env::get('APP_DEBUG', false) ? self::DEBUG : self::INFO;
    }
}
