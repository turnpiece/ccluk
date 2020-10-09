<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Beehive\Monolog\Formatter;

use Beehive\Monolog\Logger;
/**
 * Formats a log message according to the ChromePHP array format
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ChromePHPFormatter implements \Beehive\Monolog\Formatter\FormatterInterface
{
    /**
     * Translates Monolog log levels to Wildfire levels.
     */
    private $logLevels = array(\Beehive\Monolog\Logger::DEBUG => 'log', \Beehive\Monolog\Logger::INFO => 'info', \Beehive\Monolog\Logger::NOTICE => 'info', \Beehive\Monolog\Logger::WARNING => 'warn', \Beehive\Monolog\Logger::ERROR => 'error', \Beehive\Monolog\Logger::CRITICAL => 'error', \Beehive\Monolog\Logger::ALERT => 'error', \Beehive\Monolog\Logger::EMERGENCY => 'error');
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // Retrieve the line and file if set and remove them from the formatted extra
        $backtrace = 'unknown';
        if (isset($record['extra']['file'], $record['extra']['line'])) {
            $backtrace = $record['extra']['file'] . ' : ' . $record['extra']['line'];
            unset($record['extra']['file'], $record['extra']['line']);
        }
        $message = array('message' => $record['message']);
        if ($record['context']) {
            $message['context'] = $record['context'];
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
        }
        if (\count($message) === 1) {
            $message = \reset($message);
        }
        return array($record['channel'], $message, $backtrace, $this->logLevels[$record['level']]);
    }
    public function formatBatch(array $records)
    {
        $formatted = array();
        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }
        return $formatted;
    }
}