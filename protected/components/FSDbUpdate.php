<?php

class FSDbUpdate extends CDbMigration {

	private $_arFiles;

	public function init() {
		$this->_arFiles=array();
		Yii::import('application.extensions.tx.*');
	}

	private function executeFile($filePath) {
		$file = new TXFile(array(
		                   'path' => $filePath,
		                   ));

		if (!$file->exists)
			throw new Exception("'$filePath' is not a file");

		try {
			if ($file->open(TXFile::READ) === false)
				throw new Exception("Can't open '$filePath'");

			$total = floor($file->size / 1024);
			while (!$file->endOfFile()) {
				$line = $file->readLine();
				$line = trim($line);
				if (empty($line))
					continue;
				$current = floor($file->tell() / 1024);
				$this->getDbConnection()->createCommand($line)->execute();
			}

			$file->close();
		} catch (Exception $e) {
			$file->close();
			throw $e;
		}
	}

	public function addFile($filename) {
		$this->_arFiles[]=$filename;
	}

	public function safeUp() {
		foreach($this->_arFiles as $sFile) {
			$this->executeFile($sFile);
		}
	}
}