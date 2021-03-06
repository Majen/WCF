<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\option\OptionType;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * OptionTypeTimezone is an implementation of OptionType for a select box, which list the available time zones.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class OptionTypeTimezone extends AbstractOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$timezoneOptions = array();
		foreach (DateUtil::getAvailableTimezones() as $timezone) {
			$timezoneOptions[$timezone] = WCF::getLanguage()->get('wcf.global.date.timezone.'.str_replace('/', '.', strtolower($timezone)));
		}
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'selectOptions' => $timezoneOptions,
			'value' => $value
		));
		return WCF::getTPL()->fetch('optionTypeSelect');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!in_array($newValue, DateUtil::getAvailableTimezones())) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
