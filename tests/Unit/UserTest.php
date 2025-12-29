<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(
            'john.doe@example.com',
            'hashedpassword123',
            'John Doe'
        );
    }

    public function testUserCreation(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertEquals('john.doe@example.com', $this->user->getEmail());
        $this->assertEquals('hashedpassword123', $this->user->getPassword());
        $this->assertEquals('John Doe', $this->user->getName());
    }

    public function testSetId(): void
    {
        $this->user->setId(42);
        $this->assertEquals(42, $this->user->getId());
    }

    public function testSetUserType(): void
    {
        $this->user->setUserType('Celiac');
        $this->assertEquals('Celiac', $this->user->getUserType());
    }

    public function testSetRole(): void
    {
        $this->user->setRole('admin');
        $this->assertEquals('admin', $this->user->getRole());
    }

    public function testDefaultRole(): void
    {
        $newUser = new User('test@test.com', 'pass', 'Test User');
        $this->assertEquals('user', $newUser->getRole());
    }

    public function testSetIsActive(): void
    {
        $this->assertTrue($this->user->isActive());
        
        $this->user->setIsActive(false);
        $this->assertFalse($this->user->isActive());
        
        $this->user->setIsActive(true);
        $this->assertTrue($this->user->isActive());
    }

    public function testSetAvatar(): void
    {
        $this->user->setAvatar('/images/avatar.jpg');
        $this->assertEquals('/images/avatar.jpg', $this->user->getAvatar());
    }

    public function testSetBio(): void
    {
        $this->user->setBio('Passionate about gluten-free cooking');
        $this->assertEquals('Passionate about gluten-free cooking', $this->user->getBio());
    }

    public function testToArray(): void
    {
        $this->user->setId(1);
        $this->user->setUserType('Celiac');
        $this->user->setRole('moderator');
        
        $array = $this->user->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals(1, $array['id']);
        $this->assertEquals('John Doe', $array['name']);
        $this->assertEquals('john.doe@example.com', $array['email']);
        $this->assertEquals('Celiac', $array['user_type']);
        $this->assertEquals('moderator', $array['role']);
        $this->assertTrue($array['is_active']);
    }

    public function testPasswordNotInToArray(): void
    {
        $array = $this->user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    public function testEmailValidation(): void
    {
        // Valid emails
        $validUser = new User('valid@email.com', 'pass', 'User');
        $this->assertEquals('valid@email.com', $validUser->getEmail());
        
        $validUser2 = new User('user.name+tag@example.co.uk', 'pass', 'User');
        $this->assertEquals('user.name+tag@example.co.uk', $validUser2->getEmail());
    }

    public function testNameLength(): void
    {
        $longName = str_repeat('A', 100);
        $user = new User('test@test.com', 'pass', $longName);
        $this->assertEquals($longName, $user->getName());
        $this->assertEquals(100, strlen($user->getName()));
    }
}