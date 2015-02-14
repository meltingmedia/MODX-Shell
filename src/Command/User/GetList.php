<?php namespace MODX\Shell\Command\User;

use MODX\Shell\Command\ListProcessor;

/**
 * List all users for the current modX instance
 */
class GetList extends ListProcessor
{
    protected $processor = 'security/user/getlist';
    protected $headers = array(
        'id', 'username', 'active', 'sudo'
    );

    protected $name = 'user:list';
    protected $description = 'List users';

    /**
     * Format the "modUer.active" field as boolean
     *
     * @param bool $value
     *
     * @return string
     */
    protected function formatActive($value)
    {
        return $this->renderBoolean($value);
    }

    /**
     * Format the "modUser.sudo" field as boolean
     *
     * @param bool $value
     *
     * @return string
     */
    protected function formatSudo($value)
    {
        return $this->renderBoolean($value);
    }
}
