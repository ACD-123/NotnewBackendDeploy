<?php
/**
 * Created by PhpStorm.
 * User: Ather Hashmi
 * Date: 7/5/2019
 * Time: 11:05 AM
 */

namespace App\Helpers;
use Cache;
use Carbon\Carbon;
use Exception;
use Auth;
use Validator;

class DateTimeHelper
{
    const DATE_FORMAT_DEFAULT = 'Y-m-d';
    const DATE_FORMAT_DEFAULT_NO_SEPARATORS = 'Ymd';
    const DATE_TIME_FORMAT_DEFAULT = 'Y-m-d\TH:i:s';
    const DATE_TIME_FORMAT_BASIC = 'Y-m-d H:i:s';

    const LONG_DATE_FORMAT = 'D, M j, Y';
    const LONG_DATE_FORMAT_EXTENDED = 'D, F jS, Y';

    const DATE_TIME_FORMAT_NO_SEPARATORS = 'Ymd\THis'; /*output : 20190911T144030*/
    const DATE_TIME_FORMAT_FULL_NO_SEPARATORS = 'Ymd\THis\Z';

    const DATE_FORMAT_US = 'm/d/Y';
    const DATE_FORMAT_EU = 'd-M-Y';

    const TIME_FORMAT_12_HOUR = 'h:i a';
    const TIME_FORMAT_24_HOUR = 'H:i';
    const TIME_FORMAT_12_HOUR_WITH_SECONDS = 'h:i:s';
    const TIME_FORMAT_24_HOUR_WITH_SECONDS = 'H:i:s';
    const TIME_FORMAT_12_HOUR_WITHOUT_SECONDS = 'h:i';
    const TIME_FORMAT_12_HOUR_WITHOUT_LEADING_ZEROS = 'g:i a';
    const TIME_FORMAT_12_HOUR_WITHOUT_LEADING_ZEROS_CAP_MERIDIEM = 'g:i A';

    const GOOGLE_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.00\Z';

    const TZ_UTC = 'Etc/UTC';

    const MIN_INFINITE_DATE = '2010-01-01';//simulate -ve infinite date
    const MAX_INFINITE_DATE = '2040-12-31';//simulate +ve infinite date

    const DB_TIME_FORMAT = 'HH:MI am';

    public static function getDateTimeFormat_12_hours(): string
    {
        return static::DATE_FORMAT_DEFAULT . ' ' . static::TIME_FORMAT_12_HOUR;
    }

    public static function getDateTimeFormat_24_hours(): string
    {
        return static::DATE_FORMAT_DEFAULT . ' ' . static::TIME_FORMAT_24_HOUR;
    }

    public static function getDateTimeFormatWithSeconds_12_hours(): string
    {
        return static::DATE_FORMAT_DEFAULT . ' ' . static::TIME_FORMAT_12_HOUR_WITH_SECONDS;
    }

    public static function getDateTimeFormatWithSeconds_24_hours(): string
    {
        return static::DATE_FORMAT_DEFAULT . ' ' . static::TIME_FORMAT_24_HOUR_WITH_SECONDS;
    }

    public static function getDateTimeFormat_12_hours_US(): string
    {
        return static::DATE_FORMAT_US . ' ' . static::TIME_FORMAT_12_HOUR;
    }

    public static function getDateTimeFormat_24_hours_US(): string
    {
        return static::DATE_FORMAT_US . ' ' . static::TIME_FORMAT_24_HOUR;
    }

    public static function getDateTimeFormat_12_hours_EU(): string
    {
        return static::DATE_FORMAT_EU . ' ' . static::TIME_FORMAT_12_HOUR;
    }

    public static function getDateTimeFormat_24_hours_EU(): string
    {
        return static::DATE_FORMAT_EU . ' ' . static::TIME_FORMAT_24_HOUR;
    }

    public static function getDateTimeFormat_12_hours_With_TZ(): string
    {
        return static::getDateTimeFormat_12_hours_US() . ' (T)';
    }

    public static function getDateTimeFormat_24_hours_With_TZ(): string
    {
        return static::getDateTimeFormat_24_hours_US() . ' (T)';
    }

    public static function getDbDateFormat_US(): string
    {
        return 'MM/DD/YYYY';
    }

    public static function getDbDateFormat_EU(): string
    {
        return 'DD-Mon-YYYY';
    }

    public static function getPhpDateFormat_US(): string
    {
        return static::DATE_FORMAT_US;
    }

    public static function getPhpDateFormat_EU(): string
    {
        return static::DATE_FORMAT_EU;
    }

    /**
     * @param string $phpDateFormat
     * @return string
     * @throws Exception
     */
    public static function convertToDbDateFormat(string $phpDateFormat): string
    {
        if ($phpDateFormat === static::getPhpDateFormat_US()) {
            return static::getDbDateFormat_US();
        } else if ($phpDateFormat === static::getPhpDateFormat_EU()) {
            return static::getDbDateFormat_EU();
        } else if ($phpDateFormat === 'l, F d, Y') {
            return 'Day, Month DD, YYYY';
        } else if ($phpDateFormat === static::DATE_FORMAT_DEFAULT) {
            return 'YYYY-MM-DD';
        }

        throw new Exception('Cannot convert the PHP date format');
    }

    public static function getCenterDateFormat(int $centerId): string
    {
        return Cache::rememberForever("DATE_FORMAT_CACHE_{$centerId}", function () use ($centerId) {
            return Center::findOrFail($centerId, ['date_format'])->date_format;
        });
    }

    public static function getCurrentCenterDateFormat(): string
    {
        /** @var User $user */
        if (($user = Auth::user()) !== null) {
            return static::getCenterDateFormat($user->getCurrentCenterId());
        }

        return static::DATE_FORMAT_DEFAULT;
    }

    public static function getCurrentCenterDateTimeFormat(): string
    {
        $currentDateFormat = static::getCurrentCenterDateFormat();

        return $currentDateFormat === static::DATE_FORMAT_EU
            ? static::getDateTimeFormat_12_hours_EU()
            : static::getDateTimeFormat_12_hours_US();
    }

    public static function getCurrentCenterDateTimeFormat_With_TZ(): string
    {
        return static::getCurrentCenterDateTimeFormat() . ' (T)';
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getDbDateFormatForCurrentCenter(): string
    {
        return static::convertToDbDateFormat(static::getCurrentCenterDateFormat());
    }

    public static function isValidDateString(string $date = null, bool $allowEmpty = true): bool
    {
        if ($allowEmpty && empty($date)) {
            return true;
        }

        $rules = ['date'];

        if (!$allowEmpty) {
            $rules[] = 'required';
        }

        $validator = Validator::make(['date' => $date], ['date' => implode('|', $rules)]);

        return $validator->passes();
    }

    public static function parseTime(string $time): Carbon
    {
        return Carbon::today()->setTimeFromTimeString($time);
    }

    public static function formatTimeString(?string $time, string $format = self::TIME_FORMAT_24_HOUR_WITH_SECONDS): ?string
    {
        if (empty($time)) {
            return null;
        }

        return static::parseTime($time)->format($format);
    }

    public static function convertMinsToHrs($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        // format hours and minutes
        $hours = $hours < 10 ? '0' . $hours : $hours;
        $mins = $mins < 10 ? '0' . $mins : $mins;

        return $hours . ':' . $mins;
    }

    public static function diffInTechyFormat(Carbon $start, Carbon $end): string
    {
        $minutes = $end->diffInMinutes($start);
        $term = "{$minutes} MIN";

        if ($minutes >= 60) {
            $hours = $end->diffInHours($start);
            $remainingMinutes = $minutes - ($hours * 60);
            $term = "{$hours}HR"
                . ($remainingMinutes > 0
                    ? " {$remainingMinutes}MIN"
                    : ''
                );
        }

        return $term;
    }

    /**
     * @param int $quarter
     * @param int $year
     * @return array
     * @throws Exception
     */
    public static function getQuarterRange(int $quarter, int $year): array
    {
        switch ($quarter) {
            case 1:
                return [Carbon::parse("{$year}-01-01"), Carbon::parse("{$year}-03-31")];
            case 2:
                return [Carbon::parse("{$year}-04-01"), Carbon::parse("{$year}-06-30")];
            case 3:
                return [Carbon::parse("{$year}-07-01"), Carbon::parse("{$year}-09-30")];
            case 4:
                return [Carbon::parse("{$year}-10-01"), Carbon::parse("{$year}-12-31")];
        }

        throw new Exception("Invalid quarter: {$quarter}");
    }

    public static function firstDayOfFiscalYear(int $year = null, int $fiscalYearStartMonth = null, int $fiscalYearStartDay = null): Carbon
    {
        $year = $year ?? Carbon::now()->year;
        $fiscalYearStartMonth = $fiscalYearStartMonth ?? Settings::getFirstFiscalYearQuarter();
        $fiscalYearStartDay = $fiscalYearStartDay ?? Settings::getFirstFiscalYearQuarterDay();

        return Carbon::createFromDate($year, $fiscalYearStartMonth, $fiscalYearStartDay);
    }

    public static function lastDayOfFiscalYear(int $year = null, int $fiscalYearStartMonth = null, int $fiscalYearStartDay = null): Carbon
    {
        return static::firstDayOfFiscalYear($year, $fiscalYearStartMonth, $fiscalYearStartDay)
            ->addYear()
            ->subDay();// this will make sure of leap year.
    }

    public static function firstDayOfCurrentQuarter(int $startingMonthOfYear = null, int $month = null, int $year = null, int $day = null): Carbon
    {
        $today = Carbon::now();
        $month = $month ?? $today->month;
        $year = $year ?? $today->year;
        $day = $day ?? 1;
        $startingMonthOfYear = $startingMonthOfYear ?? Settings::getFirstFiscalYearQuarter();
        $firstMonthOfCurrentQuarter = $month - ($month - $startingMonthOfYear + 12) % 12 % 3;

        return Carbon::createFromDate($year, $firstMonthOfCurrentQuarter, $day);
    }

    public static function lastDayOfCurrentQuarter(int $startingMonthOfYear = null, int $month = null, int $year = null, $day = null): Carbon
    {
        return static::firstDayOfCurrentQuarter($startingMonthOfYear, $month, $year, $day)
            ->addMonths(3)// since quarter is of three months...
            ->subDay(); // last day of previous month.
    }

    /**
     * Month/QuarterNumber as Index/Value map. starting month defaults read from settings.
     * @param int $startingMonthOfYear
     * @return array
     */
    public static function quarterMonthMap($startingMonthOfYear = null): array
    {
        $start = $startingMonthOfYear ?? Settings::getFirstFiscalYearQuarter();
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = static::findQuarter($month, $start);
        }

        return $months;
    }

    /**
     * Finds: specified month is of which quarter of year, provided starting month of year.
     * By default quarter number of current month is returned if $month not provided.
     * Also starting month of year by default is 1st i.e. January
     *
     * @param string | int | Carbon | null $month
     * @param int $startingMonthOfYear
     *
     * @return int 1 - 4
     */
    public static function quarter($month = null, int $startingMonthOfYear = 1): int
    {
        if ($month === null) {
            $monthNumber = Carbon::now()->month;
        } elseif ($month instanceof Carbon) {
            /**@var Carbon $month */
            $monthNumber = $month->month;
        } elseif (is_string($month)) {
            $monthNumber = Carbon::parse($month)->month;
        } else {
            $monthNumber = $month;
        }

        return static::findQuarter($month, $startingMonthOfYear);
    }

    /**
     * Finds: specified month is of which quarter of year, saying that starting month of year is read from settings.
     * By default quarter number of current month is returned if $month not provided.
     *
     * @param string | int | Carbon | null $month
     * @return int 1 - 4
     */
    public static function quarterFromSetting($month = null): int
    {
        $startingMonthOfYear = Settings::getFirstFiscalYearQuarter();

        return static::quarter($month, $startingMonthOfYear);
    }

    private static function findQuarter(int $month, int $start = 1): int
    {
        /**
         * Here 12 represents 12 months in a year and 3 as length of one quarter in a year.
         * When start will be other than 1st month, we will back cycle.
         * (int) is 2.x faster than intval().
         */
        return (int)(($month - $start + 12) % 12 / 3 + 1);
    }

    /**
     * @param string $timestamp
     * @param string $tzCode
     * @return Carbon
     * @throws Exception
     */
    public static function setTimezone(string $timestamp, string $tzCode): Carbon
    {
        $date = Carbon::parse($timestamp);

        return $date->setTimezone($tzCode);
    }

    public static function defaultAppTimezone(): string
    {
        /**
         * Keeping the timezone consistent with the database timezone
         */

        return 'Etc/UTC';
        //return 'America/New_York';
    }

    public static function serverTimezone(): string
    {
        return 'America/New_York';
    }

    public static function now_utc()
    {
        return Carbon::now('UTC');
    }
}
