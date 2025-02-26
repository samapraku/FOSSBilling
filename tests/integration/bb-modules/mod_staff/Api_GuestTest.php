<?php

#[\PHPUnit\Framework\Attributes\Group('Core')]
class Api_Guest_StaffTest extends BBDbApiTestCase
{
    protected $_initialSeedFile = 'admins.xml';

    public function testCreate()
    {
        $data = [
            'email' => 'admin@fossbilling.org',
            'password' => 'adminPa55word',
        ];

        $this->api_admin->staff_delete(['id' => 1]);
        $this->api_guest->staff_create($data);
    }

    public function testCreateException()
    {
        try {
            $this->api_guest->staff_create();
            $this->fail('Should not allow create admin account if one already exists');
        } catch (Exception $e) {
            $this->assertEquals(55, $e->getCode());
        }
    }

    public function testLogin()
    {
        $data = [
            'email' => 'admin@fossbilling.org',
            'password' => 'demo',
        ];
        $array = $this->api_guest->staff_login($data);
        $this->assertIsArray($array);
        $this->assertIsArray($this->session->get('admin'));

        $bool = $this->api_admin->staff_profile_logout($data);
        $this->assertFalse(isset($this->session->admin));
    }
}
