<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ParticipantController;
use App\Models\User;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event as EventFacade;
use Mockery;

class ParticipantControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function invite_sends_invitation_to_existing_user()
    {
        $controller = new ParticipantController();

        // Mock Auth
        Auth::shouldReceive('id')->andReturn(1);

        // Mock User lookup
        $userMock = Mockery::mock('alias:' . User::class);
        $userMock->shouldReceive('where->first')
            ->andReturn((object)['id' => 2, 'email' => 'test@example.com']);

        // Mock Participant check
        $participantMock = Mockery::mock('alias:' . Participant::class);
        $participantMock->shouldReceive('where->where->first')->andReturn(null);
        $participantMock->shouldReceive('create')->once()->andReturnSelf();

        // Mock Event
        $event = (object)[
            'id' => 10,
            'created_by' => 1
        ];

        // Mock Event facade for InvitationCreatedEvent
        EventFacade::fake();

        // Create request
        $request = Request::create('/events/10/invite', 'POST', [
            'emails' => ['test@example.com']
        ]);

        $response = $controller->invite($request, $event);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertArrayHasKey('test@example.com', $data);
        $this->assertEquals('Invitation envoyée', $data['test@example.com']);
    }

    /** @test */
    public function respond_to_invitation_updates_status()
    {
        $controller = new ParticipantController();

        Auth::shouldReceive('id')->andReturn(2);

        $participantMock = Mockery::mock('alias:' . Participant::class);
        $participantMock->shouldReceive('where->where->first')
            ->andReturn((object)[
                'id' => 5,
                'update' => function ($data) {
                    return $data;
                },
            ]);

        $request = Request::create('/events/10/respond', 'POST', [
            'status' => 'accepted'
        ]);

        $event = (object)['id' => 10];

        $response = $controller->respondToInvitation($request, $event);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('accepted', $response->getData()->message);
    }

    /** @test */
    public function index_returns_participants()
    {
        $controller = new ParticipantController();

        Auth::shouldReceive('id')->andReturn(1);

        $participantsMock = Mockery::mock('alias:' . Participant::class);
        $participantsMock->shouldReceive('with->where->get')
            ->andReturn(collect([
                (object)['user_id' => 2, 'status' => 'accepted']
            ]));

        $event = (object)[
            'id' => 10,
            'privacy' => 'private',
            'created_by' => 1
        ];

        $response = $controller->index($event);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getData());
    }
}

