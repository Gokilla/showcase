<?php

namespace backend\tests\_support\Helper;

class Functional extends \Codeception\Module
{
    /**
     * @param \Codeception\TestInterface $test
     */
    public function _before(\Codeception\TestInterface $test): void
    {
        $this->getModule('Yii2')->client->setServerParameter('REMOTE_ADDR', '127.0.0.1');
    }

    /**
     * @return string
     */
    public function grabResponse(): string
    {
        return $this->getModule('Yii2')->_getResponseContent();
    }

    /**
     * @return array
     */
    public function grabAjaxResponse()
    {
        $content = $this->grabResponse();

        return $content ? json_decode($content, true) : [];
    }
}
