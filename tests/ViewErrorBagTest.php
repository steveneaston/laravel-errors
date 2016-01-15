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
        $this->assertEquals(' class="error-field"', $this->bag->classes('name'));
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
        $this->assertEquals(' error-field', $this->bag->singleClass('name'));
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
            '<div class="error-list"><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->render()
        );

        $this->assertEquals(
            '<div class="error-list"><ul><li>Message for email</li></ul></div>',
            $this->bag->render(null, 'email')
        );

        $this->assertEquals(
            '<div class="error-list"><ul><li>Message for email</li></ul></div>',
            $this->bag->render(null, 'email', 'error-list')
        );

        $this->assertEquals(
            '<div class="errors error-list"><ul><li>Message for email</li></ul></div>',
            $this->bag->render(null, 'email', ['errors', 'error-list'])
        );
    }

    /**
     * @test
     */
    public function it_can_alter_the_default_classes()
    {
        $this->bag->setClasses(['field' => 'buttery', 'list' => 'crunchy']);

        $this->assertEquals(' class="buttery"', $this->bag->classes('name'));

        $this->assertEquals(
            '<div class="crunchy"><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->render()
        );

        $this->assertEquals(
            '<div class="crunchy"><ul><li>Message for name</li></ul></div>',
            $this->bag->render(null, 'name')
        );
    }

    /**
     * @test
     */
    public function it_can_alter_an_individual_class()
    {
        $this->bag->setClass('field', 'buttery');

        $this->assertEquals(' class="buttery"', $this->bag->classes('name'));

        $this->assertEquals(
            '<div class="error-list"><ul><li>Message for name</li></ul></div>',
            $this->bag->render(null, 'name')
        );
    }

    /**
     * @test
     */
    public function it_returns_an_individual_error_message()
    {
        $this->assertEquals(
            '<div class="error-fieldList"><ul><li>Message for name</li></ul></div>',
            $this->bag->field('name')
        );
    }

    /**
     * @test
     */
    public function it_can_include_a_message_with_render()
    {
        // Default error message
        $this->assertEquals(
            '<div class="error-fieldList has-message"><p>There was a problem with your input.</p><ul><li>Message for name</li></ul></div>',
            $this->bag->withMessage()->field('name')
        );

        // Custom error message
        $this->assertEquals(
            '<div class="error-fieldList has-message"><p>There was a problem.</p><ul><li>Message for name</li></ul></div>',
            $this->bag->withMessage('There was a problem.')->field('name')
        );

        // Custom error message
        $this->assertEquals(
            '<div class="error-list has-message"><p>There were some problems.</p><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->withMessage('There was a problem.|There were some problems.')->render()
        );

        // Message should only be included the first time
        $this->assertEquals(
            '<div class="error-fieldList"><ul><li>Message for name</li></ul></div>',
            $this->bag->field('name')
        );
    }

    /**
     * @test
     */
    public function it_can_use_parameters_to_render_a_list_message()
    {
        // Custom message
        $this->assertEquals(
            '<div class="error-list has-message"><p>There were problems accessing the biscuit tin.</p><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->render('There were problems accessing the biscuit tin.')
        );

        // Default message
        $this->assertEquals(
            '<div class="error-list has-message"><p>There was a problem with your input.</p><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->render(true)
        );

        // Individual field
        $this->assertEquals(
            '<div class="error-list has-message"><p>Whoops!</p><ul><li>Message for name</li></ul></div>',
            $this->bag->render('Whoops!', 'name')
        );

        // All fields, custom error class
        $this->assertEquals(
            '<div class="custom-error-message has-message"><p>Whoops!</p><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->render('Whoops!', null, 'custom-error-message')
        );

        // Individual field version
        $this->assertEquals(
            '<div class="error-fieldList has-message"><p>Whoops!</p><ul><li>Message for name</li></ul></div>',
            $this->bag->field('name', 'Whoops!')
        );

    }

    /**
     * @test
     */
    public function it_can_alter_the_default_messages()
    {
        $this->bag->setMessages(['list' => 'There were problems accessing the biscuit tin.']);

        $this->assertEquals(
            '<div class="error-fieldList has-message"><p>There were problems accessing the biscuit tin.</p><ul><li>Message for name</li></ul></div>',
            $this->bag->withMessage()->field('name')
        );

        $this->assertEquals(
            '<div class="error-list has-message"><p>There were problems accessing the biscuit tin.</p><ul><li>Message for name</li><li>Message for email</li><li>Message for password</li></ul></div>',
            $this->bag->withMessage()->render()
        );
    }

    /**
     * @test
     */
    public function it_can_alter_an_individual_default_message()
    {
        $this->bag->setMessage('list', 'There were problems accessing the biscuit tin.');

        $this->assertEquals(
            '<div class="error-fieldList has-message"><p>There were problems accessing the biscuit tin.</p><ul><li>Message for name</li></ul></div>',
            $this->bag->withMessage()->field('name')
        );
    }

    /**
     * @test
     */
    public function it_can_render_an_array_of_keys()
    {
        $this->assertEquals(
            '<div class="error-list"><ul><li>Message for name</li><li>Message for email</li></ul></div>',
            $this->bag->render(null, ['name', 'email'])
        );

        // Display in given order
        $this->assertEquals(
            '<div class="error-fieldList"><ul><li>Message for email</li><li>Message for name</li></ul></div>',
            $this->bag->field(['email', 'name'])
        );

        // Handle only one field given as an array
        $this->assertEquals(
            '<div class="error-fieldList"><ul><li>Message for email</li></ul></div>',
            $this->bag->field(['email'])
        );

    }

}
