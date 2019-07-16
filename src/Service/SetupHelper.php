<?php

namespace Shapecode\Devliver\Service;

/**
 * Class SetupHelper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class SetupHelper
{

    /**
     * @return bool
     */
    public function isNeeded(): bool
    {
        if ($this->isGeneralSetupNeeded()) {
            return true;
        }

        if ($this->isDatabaseSetupNeeded()) {
            return true;
        }

        if ($this->isMailerSetupNeeded()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isGeneralSetupNeeded(): bool
    {
        if (getenv('DEVLIVER_HOST') === false) {
            return true;
        }

        if (getenv('LOCALE') === false) {
            return true;
        }

        if (getenv('APP_SECRET') === false) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDatabaseSetupNeeded(): bool
    {
        return getenv('DATABASE_URL') === false;
    }

    /**
     * @return bool
     */
    public function isMailerSetupNeeded(): bool
    {
        if (getenv('MAILER_URL') === false) {
            return true;
        }
        if (getenv('MAILER_SENDER') === false) {
            return true;
        }

        return false;
    }
}
