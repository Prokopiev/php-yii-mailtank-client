<?php
class SubscriberTest extends Mailtank_TestCase
{

    public static function createBasicModel()
    {
        $model = new MailtankSubscriber();
        $id = uniqid();
        $model->setAttributes(array(
            'id' => $id,
            'email' => $id . "@example.com",
        ));

        return $model;
    }


    public function testCreate()
    {
        $subscriber = self::createBasicModel();

        $subscriber->tags = array('test1', 'test2');

        $subscriber->setProperties(array(
            'property1' => 1,
            'property2' => 0,
            'property3' => 3,
        ));
        $subscriber->setProperty('property2', 2);

        $unsavedModel = clone $subscriber;
        $this->assertTrue($subscriber->save());

        $this->assertEquals($subscriber->id, $unsavedModel->id);
        $this->assertEquals($subscriber->email, $unsavedModel->email);

        $this->assertContains(
            array(
                'property1' => 1,
                'property2' => 2,
                'property3' => 3,
            ),
            $subscriber->attributes
        );

        $this->assertContains(
            array(
                'test1',
                'test2',
            ),
            $subscriber->attributes
        );

        $this->assertNotNull($subscriber->id);
    }

    public function testGetById()
    {
        $savedModel = self::createBasicModel();
        $this->assertTrue($savedModel->save());

        $subscriber = MailtankSubscriber::findByPk($savedModel->id);
        $this->assertEquals($savedModel->attributes, $subscriber->attributes);
    }

    public function testUpdate()
    {
        $savedModel = self::createBasicModel();
        $savedModel->tags = array('test1', 'test2');

        $savedModel->setProperties(array(
            'property1' => 1,
            'property2' => 0,
            'property3' => 3,
        ));
        $savedModel->setProperty('property2', 2);
        $this->assertTrue($savedModel->save());

        $model = clone $savedModel;

        $newEmail = uniqid() . '@example.com';

        $model->setProperty('property2', 2);
        $model->setProperty('property4', 4);
        $model->tags = array('test2', 'test3');
        $model->email = $newEmail;

        $this->assertTrue($model->save());

        /** @var $_model MailtankSubscriber */
        foreach (array($model, $model::findByPk($model->id)) as $_model) {
            $this->assertEquals($model->id, $_model->id);
            $this->assertEquals($newEmail, $_model->email);

            $this->assertContains(
                array(
                    'property1' => 1,
                    'property2' => 2,
                    'property3' => 3,
                    'property4' => 4,
                ),
                $_model->attributes
            );

            $this->assertContains(
                array(
                    'test2',
                    'test3',
                ),
                $_model->attributes
            );
        }
    }

    public function testDelete()
    {
        $model = self::createBasicModel();
        $this->assertTrue($model->save());

        $this->assertTrue($model->delete());
        $this->assertFalse(MailtankSubscriber::findByPk($model->id));
    }

    public function testRefresh()
    {
        $savedModel = $this->createBasicModel();

        $e = false;
        try {
            $savedModel->refresh();
        } catch (MailtankException $e) {
            $e = true;
        }
        $this->assertTrue($e, 'Updated model cant be refreshed');
        $this->assertTrue($savedModel->save());
        $this->assertTrue($savedModel->refresh());
    }

    public function testPatchTags()
    {
        $subscribers = array();
        $subscribers_id = array();
        for ($i = 0; $i < 2; $i++) {
            $subscriber = $this->createBasicModel();
            $this->assertTrue($subscriber->save());
            $subscribers[] = $subscriber;
            $subscribers_id[] = $subscriber->id;
        }

        $tag = 'test_tag_' . uniqid();
        $result = MailtankSubscriber::patchTags($subscribers_id, $tag);
        $this->assertTrue($result);

        foreach ($subscribers as $subscriber) {
            $this->assertTrue($subscriber->refresh());
            $this->assertContains($tag, $subscriber->tags);
        }
    }

    public function testPatchTagsAll()
    {
        $subscribers = array();
        $subscribers_id = array();
        for ($i = 0; $i < 2; $i++) {
            $subscriber = $this->createBasicModel();
            $this->assertTrue($subscriber->save());
            $subscribers[] = $subscriber;
            $subscribers_id[] = $subscriber->id;
        }

        $tag = 'test_tag_' . uniqid();
        $result = MailtankSubscriber::patchTags('all', $tag);
        $this->assertTrue($result);

        foreach ($subscribers as $subscriber) {
            $this->assertTrue($subscriber->refresh());
            $this->assertContains($tag, $subscriber->tags);
        }
    }

    public function testEmailAsId()
    {
        $subscriber = self::createBasicModel();
        $subscriber->id = $subscriber->email;
        $this->assertTrue($subscriber->save());
        $this->assertTrue($subscriber->refresh());
        $this->assertTrue($subscriber->delete());
        $this->assertFalse($subscriber->refresh());
    }
}