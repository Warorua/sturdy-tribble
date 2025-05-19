<?php
class Logger
{
	private $conn;

	public function __construct($conn)
	{
		$this->conn = $conn;
	}

	public function log($level, $message, $context = null, $stackTrace = null)
	{
        $context = $context ?? $_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN';
		try {
			$sql = "INSERT INTO system_logs (level, message, context, stack_trace) VALUES (:level, :message, :context, :stack_trace)";
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([
				':level' => strtoupper($level),
				':message' => $message,
				':context' => $context,
				':stack_trace' => $stackTrace
			]);
		} catch (PDOException $e) {
			// Fallback: write to file if DB logging fails
			error_log("[DB-LOGGING-FAIL] " . $e->getMessage());
		}
	}

	public function info($message, $context = null) {
		$this->log('INFO', $message, $context);
	}

	public function warning($message, $context = null) {
		$this->log('WARNING', $message, $context);
	}

	public function error($message, $context = null, $stackTrace = null) {
		$this->log('ERROR', $message, $context, $stackTrace);
	}

	public function critical($message, $context = null, $stackTrace = null) {
		$this->log('CRITICAL', $message, $context, $stackTrace);
	}
}
