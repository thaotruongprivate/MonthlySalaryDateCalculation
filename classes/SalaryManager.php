<?php

/**
 * Class SalaryManager
 * this class is responsible for handling matters related to salary and bonus,
 * such as generating a report on on which dates each month they should be paid
 */
class SalaryManager {

	const FIRST_DATE_FORMAT = 'Y-m-01';
	const DATE_FORMAT = 'Y-m-d';
	const DEFAULT_OUTPUT_FILE_EXTENSION = 'csv';
	const DEFAULT_CALCULATION_PERIOD = 12; // the default amount of months that salary dates should be calculated for
	const HEADER_NAMES = ['Month', 'Salary Date', 'Bonus Date'];

	/**
	 * @param null|string $fileName
	 * @param int $noOfMonths
	 */
	public function generateNextMonthlySalaryDates($noOfMonths = self::DEFAULT_CALCULATION_PERIOD, $fileName = null) {

		// the first month we calculate is the next month from now
		$calculationMonth = new DateTime(date(self::FIRST_DATE_FORMAT, strtotime('+1 month')));

		if (!$fileName) {
			$fileName = self::getDefaultFileName($noOfMonths);
		}

		$handle = fopen(self::getOutputFolder() . $fileName, 'w+');

		fputcsv($handle, self::HEADER_NAMES);

		$monthsProcessed = 0;

		while ($monthsProcessed < $noOfMonths) {

			$month = $calculationMonth->format('m');
			$year = $calculationMonth->format('Y');

			$salary = new MonthlySalary($month, $year);
			$bonus = new MonthlyBonus($month, $year);

			$salaryDate = $salary->getSalaryDate()->format(self::DATE_FORMAT);

			$bonusDate = $bonus->getBonusDate()->format(self::DATE_FORMAT);

			echo "{$calculationMonth->format('m/Y')}: Salary date is {$salaryDate}, bonus date is {$bonusDate}\n";

			fputcsv(
				$handle,
				[
					$calculationMonth->format('m/Y'),
					$salaryDate,
					$bonusDate,
				]
			);

			$calculationMonth->modify('+1 month');
			$monthsProcessed++;
		}

		fclose($handle);
	}

	/**
	 * @return string
	 */
	public static function getOutputFolder() {
		return __DIR__ . '/../csv/';
	}

	/**
	 * @param int $noOfMonths
	 * @return string
	 */
	public static function getDefaultFileName($noOfMonths = self::DEFAULT_CALCULATION_PERIOD) {
		$calculationMonth = new DateTime(date(self::FIRST_DATE_FORMAT, strtotime('+1 month')));
		$lastMonthToCalculate = date('m.Y', strtotime("+{$noOfMonths} month"));
		return "{$calculationMonth->format('m.Y')}-{$lastMonthToCalculate}." . self::DEFAULT_OUTPUT_FILE_EXTENSION;
	}
}