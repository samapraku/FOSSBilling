<?php
/**
 * Copyright 2022-2023 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Cron;

class Service
{
    protected ?\Pimple\Container $di;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function getCronInfo()
    {
        $service = $this->di['mod_service']('system');

        $result = [
            'cron_url' => BB_URL . 'cron.php',
            'cron_path' => PATH_ROOT . DIRECTORY_SEPARATOR . 'cron.php',
            'last_cron_exec' => $service->getParamValue('last_cron_exec'),
        ];

        return $result;
    }

    /**
     * @param null $interval - parameter from CLI, pass to filter crons to run
     *
     * @return bool
     *
     * @todo finish fixing, time to sleep
     */
    public function runCrons($interval = null)
    {
        $api = $this->di['api_system'];
        $this->di['logger']->setChannel('cron')->info('Started executing cron jobs');

        // @core tasks
        $this->_exec($api, 'hook_batch_connect');
        $this->di['events_manager']->fire(['event' => 'onBeforeAdminCronRun']);

        $this->_exec($api, 'invoice_batch_pay_with_credits');
        $this->_exec($api, 'invoice_batch_activate_paid');
        $this->_exec($api, 'invoice_batch_send_reminders');
        $this->_exec($api, 'invoice_batch_generate');
        $this->_exec($api, 'invoice_batch_invoke_due_event');
        $this->_exec($api, 'order_batch_suspend_expired');
        $this->_exec($api, 'order_batch_cancel_suspended');
        $this->_exec($api, 'support_batch_ticket_auto_close');
        $this->_exec($api, 'support_batch_public_ticket_auto_close');
        $this->_exec($api, 'client_batch_expire_password_reminders');
        $this->_exec($api, 'cart_batch_expire');
        $this->_exec($api, 'email_batch_sendmail');

        $create = (APPLICATION_ENV == 'production');
        $ss = $this->di['mod_service']('system');
        $ss->setParamValue('last_cron_exec', date('Y-m-d H:i:s'), $create);

        $this->di['events_manager']->fire(['event' => 'onAfterAdminCronRun']);

        $this->di['logger']->setChannel('cron')->info('Finished executing cron jobs');

        return true;
    }

    /**
     * @param string $method
     */
    protected function _exec($api, $method, $params = null)
    {
        try {
            $api->{$method}($params);
        } catch (\Exception $e) {
            throw new \Exception($e);
        } finally {
            if (php_sapi_name() == 'cli') {
                echo "\e[32mSuccessfully ran " . $method . '(' . $params . ')' . ".\e[0m\n";
            }
        }
    }

    /**
     * @return string|null
     */
    public function getLastExecutionTime()
    {
        $service = $this->di['mod_service']('system');
        $last_exec = $service->getParamValue('last_cron_exec');

        return $last_exec;
    }

    public function isLate()
    {
        $t1 = new \DateTime($this->getLastExecutionTime());
        $t2 = new \DateTime('-6min');

        return $t1 < $t2;
    }
}
