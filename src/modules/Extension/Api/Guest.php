<?php
/**
 * Copyright 2022-2023 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Extension\Api;

/**
 * Extensions.
 */
class Guest extends \Api_Abstract
{
    /**
     * Checks if extensions is available.
     *
     * @param string $mod - module name to be checked
     *
     * @return bool
     */
    public function is_on($data)
    {
        $service = $this->getService();
        if (isset($data['mod']) && !empty($data['mod'])) {
            return $service->isExtensionActive('mod', $data['mod']);
        }

        if (isset($data['id']) && !empty($data['type'])) {
            return $service->isExtensionActive($data['type'], $data['id']);
        }

        return true;
    }

    /**
     * Return active theme info.
     *
     * @return array
     */
    public function theme($client = true)
    {
        $systemService = $this->di['mod_service']('theme');

        return $systemService->getThemeConfig($client, null);
    }

    /**
     * Retrieve extension public settings.
     *
     * @param string $ext - extension name
     *
     * @return array
     *
     * @throws \Box_Exception
     */
    public function settings($data)
    {
        if (!isset($data['ext'])) {
            throw new \Box_Exception('Parameter ext is missing');
        }
        $service = $this->getService();
        $config = $service->getConfig($data['ext']);

        return (isset($config['public']) && is_array($config['public'])) ? $config['public'] : [];
    }

    /**
     * Retrieve list of available languages.
     *
     * @return array
     */
    public function languages($deep = false)
    {
        return \FOSSBilling\i18n::getLocales($deep);
    }
}
