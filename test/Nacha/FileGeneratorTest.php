<?php

namespace Nacha;

use Nacha\Record\DebitEntry;
use Nacha\Record\CcdEntry;

class FileGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function setup() {
		$this->file = new FileGenerator();
		$this->file->getHeader()->setPriorityCode(1)
			->setImmediateDestination('051000033')
			->setImmediateOrigin('059999997')
			->setFileCreationDate('060210')
			->setFileCreationTime('2232')
			->setFormatCode('1')
			->setImmediateDestinationName('ImdDest Name')
			->setImmediateOriginName('ImdOriginName')
			->setReferenceCode('Reference');
	}

	public function testBatchesEntryCount() {
		// when
		$this->file->addBatch($this->getBatch());
		$this->file->addBatch($this->getBatch());

		// then
		$this->assertEquals('0000001', (string)$this->file->getBatches()[0]->getHeader()->getBatchNumber());
		$this->assertEquals('0000002', (string)$this->file->getBatches()[1]->getHeader()->getBatchNumber());
	}

	public function testBatchesAndEntries() {
		// given
		$batchA = $this->getBatch();

		$batchB = $this->getBatch();
		$batchB->getHeader()->setEntryDescription('EXPENSES');

		// when
		$this->file->addBatch($batchA);
		$this->file->addBatch($batchB);

		// then
		$this->assertEquals("101 051000033 0599999970602102232A094101ImdDest Name           ImdOriginName          Referenc
5225MY BEST COMP    INCLUDES OVERTIME   1419871234PPDPAYROLL   0602  0112     2010212340000001
62709101298746479999         0000055000SomePerson1255 Alex Dubrovsky        S 0099936340000015
822500000100091012980000000550000000000000001419871234                         010212340000001
5225MY BEST COMP    INCLUDES OVERTIME   1419871234PPDEXPENSES  0602  0112     2010212340000002
62709101298746479999         0000055000SomePerson1255 Alex Dubrovsky        S 0099936340000015
822500000100091012980000000550000000000000001419871234                         010212340000002
9000002000001000000020009101298000000110000000000000000                                       ", (string)$this->file);
	}

	private function getBatch() {
		$batch = new Batch();
		$batch->getHeader()
			->setCompanyName('MY BEST COMP')
			->setCompanyDiscretionaryData('INCLUDES OVERTIME')
			->setCompanyId('1419871234')
			->setStandardEntryClassCode('PPD')
			->setEntryDescription('PAYROLL')
			->setCompanyDescriptiveDate('0602')
			->setEffectiveEntryDate('0112')
			->setOriginatorStatusCode('2')
			->setOriginatingDFiId('01021234');

		$batch->addDebitEntry((new DebitEntry)
			->setRecordTypeCode(6)
			->setTransactionCode(27)
			->setReceivingDfiId('09101298')
			->setCheckDigit(7)
			->setDFiAccountNumber('46479999')
			->setAmount('55000')
			->setIndividualId('SomePerson1255')
			->setIdividualName('Alex Dubrovsky')
			->setDiscretionaryData('S')
			->setAddendaRecordIndicator(0)
			->setTraceNumber('99936340000015'));

		return $batch;
	}

}