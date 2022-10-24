<?php

namespace tests\api;

/**
 * @mixin \Codeception\Test\Unit
 */
class TestGeneralPageCest
{
//    public function _fixtures()
//    {
//        return ['posts' => PostsFixture::className()];
//    }

    // tests
    public function createNewUser(\ApiTester $I)
    {
        $I->wantTo('Открыть ссылку с API');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"errors":{"message":"Page not found."}}');
    }
}
