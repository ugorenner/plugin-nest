<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../core/php/nest.inc.php';

class nest extends eqLogic {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

    public static function pull() {
        foreach (nest::byType('nest') as $eqLogic) {
            if ($eqLogic->getIsEnable() == 1) {
                $eqLogic->updateFromNest();
            }
        }
    }

    public static function getNestApi() {
        if (config::byKey('username', 'nest') == '' || config::byKey('password', 'nest') == '') {
            throw new Exception(__('Aucun nom d\'utilisateur ou mot de passe défini', __FILE__));
        }
        return new nest_api(config::byKey('username', 'nest'), config::byKey('password', 'nest'));
    }

    public static function syncWithNest() {
        $nest_api = self::getNestApi();
        foreach ($nest_api->getDevices() as $thermostat) {
            $eqLogic = nest::byLogicalId($thermostat, 'nest');
            if (!is_object($eqLogic)) {
                $eqLogic = new nest();
                $eqLogic->setName($thermostat);
                $eqLogic->setEqType_name('nest');
                $eqLogic->setIsVisible(1);
                $eqLogic->setIsEnable(1);
                $eqLogic->setCategory('heating', 1);
                $eqLogic->setLogicalId($thermostat);
                $eqLogic->setConfiguration('nest_type', 'thermostat');
            }
            $device_info = $nest_api->getDeviceInfo($thermostat);
            $eqLogic->setConfiguration('local_ip', $device_info->network->local_ip);
            $eqLogic->setConfiguration('local_mac', $device_info->network->mac_address);
            $eqLogic->save();

            $cmd = $eqLogic->getCmd(null, 'temperature');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('temperature');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Température', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setEventOnly(1);
                $cmd->setOrder(6);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'humidity');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('humidity');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Humidité', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setEventOnly(1);
                $cmd->setOrder(7);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'heat');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('heat');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Chauffage', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setOrder(2);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'fan');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('fan');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Ventilation', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setOrder(3);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'auto_away');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('auto_away');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Absence automatique', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setOrder(5);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'manual_away');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('manual_away');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Absence', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setOrder(4);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }

            $order = $eqLogic->getCmd(null, 'order');
            if (!is_object($order)) {
                $order = new nestCmd();
                $order->setLogicalId('order');
                $order->setIsVisible(0);
                $order->setName(__('Consigne', __FILE__));
                $order->setType('info');
                $order->setSubType('numeric');
                $order->setEqLogic_id($eqLogic->getId());
                $order->save();
            }

            $cmd = $eqLogic->getCmd(null, 'thermostat');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('thermostat');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Thermostat', __FILE__));
                $cmd->setType('action');
                $cmd->setSubType('slider');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setTemplate('dashboard', 'thermostat');
                $cmd->setTemplate('mobile', 'thermostat');
                $cmd->setValue($order->getId());
                $cmd->setOrder(8);
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'away_on');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('away_on');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Absent', __FILE__));
                $cmd->setType('action');
                $cmd->setSubType('other');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setOrder(11);
                $cmd->save();
            }

            $cmd = $eqLogic->getCmd(null, 'away_off');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('away_off');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Présent', __FILE__));
                $cmd->setType('action');
                $cmd->setSubType('other');
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setOrder(12);
                $cmd->save();
            }
            $eqLogic->updateFromNest();
        }
        foreach ($nest_api->getDevices(DEVICE_TYPE_PROTECT) as $protects) {
            $eqLogic = nest::byLogicalId($protects, 'nest');
            if (!is_object($eqLogic)) {
                $eqLogic = new nest();
                $eqLogic->setName($protects);
                $eqLogic->setEqType_name('nest');
                $eqLogic->setIsVisible(1);
                $eqLogic->setIsEnable(1);
                $eqLogic->setCategory('security', 1);
                $eqLogic->setLogicalId($protects);
                $eqLogic->setConfiguration('nest_type', 'protect');
            }
            $eqLogic->save();
            $cmd = $eqLogic->getCmd(null, 'co_status');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('co_status');
                $cmd->setIsVisible(1);
                $cmd->setName(__('CO', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setDisplay('invertBinary', 1);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }
            $cmd = $eqLogic->getCmd(null, 'smoke_status');
            if (!is_object($cmd)) {
                $cmd = new nestCmd();
                $cmd->setLogicalId('smoke_status');
                $cmd->setIsVisible(1);
                $cmd->setName(__('Fumée', __FILE__));
                $cmd->setType('info');
                $cmd->setSubType('binary');
                $cmd->setEventOnly(1);
                $cmd->setDisplay('invertBinary', 1);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->save();
            }
            $eqLogic->updateFromNest();
        }
        self::pull();
    }

    /*     * *********************Methode d'instance************************* */

    public function updateFromNest() {
        try {
            $nest_api = nest::getNestApi();
            $device_info = $nest_api->getDeviceInfo($this->getLogicalId());
        } catch (Exception $e) {
            log::add('nest', 'error', __('Erreur sur ', __FILE__) . $this->getName() . ' : ' . $e->getMessage());
            return;
        }
        $this->setConfiguration('local_ip', $device_info->network->local_ip);
        $this->setConfiguration('local_mac', $device_info->network->mac_address);

        /*         * ********************PROTECT NEST********************** */
        if ($this->getConfiguration('nest_type') == 'protect') {
            $cmd = $this->getCmd(null, 'co_status');
            if (is_object($cmd)) {
                if ($cmd->execCmd() === '' || $cmd->execCmd() != $cmd->formatValue($device_info->co_status)) {
                    $cmd->setCollectDate('');
                    $cmd->event($device_info->co_status);
                }
            }
            $cmd = $this->getCmd(null, 'smoke_status');
            if (is_object($cmd)) {
                if ($cmd->execCmd() === '' || $cmd->execCmd() != $cmd->formatValue($device_info->smoke_status)) {
                    $cmd->setCollectDate('');
                    $cmd->event($device_info->smoke_status);
                }
            }
            $this->setConfiguration('battery_level', $device_info->battery_level);
            $this->setConfiguration('battery_health_state', $device_info->battery_health_state);
            $this->setConfiguration('replace_by_date', $device_info->replace_by_date);
            $this->setConfiguration('last_update', $device_info->last_update);
            $this->setConfiguration('last_manual_test', $device_info->last_manual_test);
            $testOk = true;
            foreach ($device_info->tests_passed as $key => $value) {
                $this->setConfiguration('test_' . $key, $value);
                if ($value != 1) {
                    $testOk = false;
                    log::add('nest', 'error', __('Echec du test : ', __FILE__) . $key . __(' sur ', __FILE__) . $this->getHumanName(), 'nestTest' . $key);
                }
            }
            if ($testOk) {
                message::removeAll('nest', 'nestTest', true);
            }
        }

        /*         * ********************THERMOSTAT NEST********************** */
        if ($this->getConfiguration('nest_type') == 'thermostat') {
            $this->setConfiguration('wan_ip', $device_info->network->wan_ip);
            $this->setConfiguration('last_connection', $device_info->network->last_connection);
            $this->setConfiguration('ac', $device_info->current_state->ac);
            $this->setConfiguration('battery_level', $device_info->current_state->battery_level);

            foreach ($device_info->current_state as $key => $value) {
                $cmd = $this->getCmd(null, $key);
                if (is_object($cmd) && ($cmd->execCmd() === '' || $cmd->execCmd() != $cmd->formatValue($value))) {
                    $cmd->setCollectDate('');
                    $cmd->event($value);
                }
            }
            $temperatures = $device_info->target->temperature;
            $order = $this->getCmd(null, 'order');
            if (is_object($order)) {
                if (is_array($temperatures)) {
                    $temperature = array_sum($temperatures) / count($temperatures);
                } else {
                    $temperature = $temperatures;
                }
                if ($order->execCmd() === '' || $order->execCmd() != $order->formatValue($temperature)) {
                    $order->setCollectDate('');
                    $order->event($temperature);
                }
            }
        }
        $this->save();
    }

    /*     * **********************Getteur Setteur*************************** */
}

class nestCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        $nest_api = nest::getNestApi();
        if ($this->getLogicalId() == 'thermostat') {
            $nest_api->setTargetTemperature($_options['slider'], $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'fan_mode_on') {
            $nest_api->setFanMode(FAN_MODE_ON, $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'fan_mode_off') {
            $nest_api->setFanMode(FAN_MODE_OFF, $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'off') {
            $nest_api->turnOff($eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'away_on') {
            $nest_api->setAway(AWAY_MODE_ON, $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'away_off') {
            $nest_api->setAway(AWAY_MODE_OFF, $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'auto_away_on') {
            $nest_api->setAutoAwayEnabled(true, $eqLogic->getLogicalId());
        }
        if ($this->getLogicalId() == 'auto_away_off') {
            $nest_api->setAutoAwayEnabled(false, $eqLogic->getLogicalId());
        }
        $eqLogic->updateFromNest();
    }

    /*     * **********************Getteur Setteur*************************** */
}
