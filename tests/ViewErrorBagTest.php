<?php

use Illuminate\Support\MessageBag;
use Seaston\LaravelErrors\ViewErrorBag;

class ViewErrorBagTest extends PHPUnit_Framework_TestCase
{
    protected $bag;

    public function tearDown() {
        Mockery::close();
    }

    /**
     * @before
     */
    public function setupErrorBag()
    {
        $bag = new ViewErrorBag;

        $messages = new MessageBag([
                'name' => ['Message for name'],
                'email' => ['Message for email'],
                'password' => ['Message for password']
            ]);

        $bag->put('default', $messages);

        $this->bag = $bag;
    }

    /**
     * @test
     */
    public function it_is_instantiable()
    {
        $this->assertInstanceOf(ViewErrorBag::class, $this->bag);
    }

    /**
     * @test
     */
    public function it_loads_an_existing_view_error_bag_from_a_request()
    {
        $request = Mockery::mock(Illuminate\Http\Request::class);

        $messageBags = $this->bag->getBags();

        $request
            ->shouldReceive('session')
            ->once()
            ->andReturn(Mockery::self()) // Session

            ->shouldReceive('get')
            ->once()
            ->andReturn(Mockery::self()) // ViewErrorBag

            ->shouldReceive('getBags')
            ->once()
            ->andReturn($messageBags); // MessageBags

        $bag = new ViewErrorBag;
        $bag->make($request);

        $this->assertEquals(3, $bag->count());
    }

    /**
     * @test
     */
    public function it_has_a_default_bag()
    {
        $bag = new ViewErrorBag;
        $this->assertCount(0, $bag);

        $this->assertCount(3, $this->bag);
    }

    /**
     * @test
     */
    public function it_checks_any_of_the_given_keys_are_set()
    {
        $this->assertTrue($this->bag->has('name'));
        $this->assertFalse($this->bag->has('biscuit'));
        $this->assertTrue($this->bag->has('email', 'password'));
        $this->assertTrue($this->bag->has(['email', 'password']));
        $this->assertTrue($this->bag->has('email', 'biscuit'));

        $this->assertTrue($this->bag->hasAny('email', 'password'));
    }

    /**
     * @test
     */
    public function it_checks_all_of_the_given_keys_are_set()
    {
        $this->assertTrue($this->bag->hasAll('name'));
        $this->assertFalse($this->bag->hasAll('biscuit'));
        $this->assertTrue($this->bag->hasAll('email', 'password'));
        $this->assertTrue($this->bag->hasAll(['email', 'password']));
        $this->assertFalse($this->bag->hasAll('email', 'biscuit'));
    }

    /**
     * @test
     */
    public function it_returns_an_html_class_parameter()
    {
        // Return default and given classes when there is an error
        $this->assertEquals(' class="field-error"', $this->bag->classes('name'));
        $this->assertEquals(' class="form-error"', $this->bag->classes('name', 'form-error'));

        // Error classes are first and can be separated by a pipe
        $this->assertEquals(' class="form-error required"', $this->bag->classes('name', 'form-error|required'));
        $this->assertEquals(' class="form-error required field"', $this->bag->classes('name', 'form-error|required field'));

        // Classes can be passed as arrays
        $this->assertEquals(' class="form-error field"', $this->bag->classes('name', ['form-error', 'field']));
        $this->assertEquals(' class="form-error required field"', $this->bag->classes('name', ['form-error|required', 'field']));

        // Return default classes when there is not an error
        $this->assertEquals(' class="field"', $this->bag->classes('biscuit', 'error field'));
        $this->assertEquals(' class="field"', $this->bag->classes('biscuit', 'error|form-error field'));
    }

    /**
     * @test
     */
    public function it_returns_a_string_off_class_names()
    {
        // Return default and given classes when there is an error
        $this->assertEquals(' field-error', $this->bag->singleClass('name'));
        $this->assertEquals(' form-error', $this->bag->singleClass('name', 'form-error'));

        // Error classes are first and can be separated by a pipe
        $this->assertEquals(' form-error required', $this->bag->singleClass('name', 'form-error|required'));
        $this->assertEquals(' form-error required field', $this->bag->singleClass('name', 'form-error|required field'));

        // Classes can be passed as arrays
        $this->assertEquals(' form-error field', $this->bag->singleClass('name', ['form-error', 'field']));
        $this->assertEquals(' form-error required field', $this->bag->singleClass('name', ['form-error|required', 'field']));

        // Return default classes when there is not an error
        $this->assertEquals(' field', $this->bag->singleClass('biscuit', 'error field'));
        $this->assertEquals(' field', $this->bag->singleClass('biscuit', 'error|form-error field'));
    }

    /**
     * @test
     */
    public function it_returns_an_unordered_list()
    {
        $this->assertEquals(
            '<ul class="error-desc"><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul>',
            $this->bag->render()
        );

        $this->assertEquals(
            '<ul class="error-desc"><li>Message for email</li></ul>',
            $this->bag->render('email')
        );

        $this->assertEquals(
            '<ul class="error-list"><li>Message for email</li></ul>',
            $this->bag->render('email', 'error-list')
        );

        $this->assertEquals(
            '<ul class="errors error-list"><li>Message for email</li></ul>',
            $this->bag->render('email', ['errors', 'error-list'])
        );
    }
}
