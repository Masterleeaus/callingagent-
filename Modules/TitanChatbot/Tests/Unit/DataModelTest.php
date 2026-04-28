<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\AI\DataModels\BookingIntentModel;
use Modules\TitanChatbot\AI\DataModels\EscalationDecisionModel;
use Modules\TitanChatbot\AI\DataModels\QuoteRequestModel;
use Modules\TitanChatbot\AI\DataModels\TrainingResultModel;
use Modules\TitanChatbot\AI\DataModels\VoiceResponseModel;

class DataModelTest extends TestCase
{
    public function test_booking_intent_from_array()
    {
        $model = BookingIntentModel::fromArray([
            'isBookingIntent' => true,
            'confidence'      => 0.95,
            'customerName'    => 'John Doe',
            'preferredDate'   => '2026-05-01',
            'contactNumber'   => '+61400000000',
        ]);

        $this->assertTrue($model->isBookingIntent);
        $this->assertSame(0.95, $model->confidence);
        $this->assertSame('John Doe', $model->customerName);
        $this->assertTrue($model->isValid());
        $this->assertTrue($model->isComplete());
    }

    public function test_booking_intent_incomplete_without_date()
    {
        $model = BookingIntentModel::fromArray([
            'isBookingIntent' => true,
            'customerName'    => 'Jane',
            // no preferredDate or contactNumber
        ]);

        $this->assertFalse($model->isComplete());
    }

    public function test_escalation_decision_defaults()
    {
        $model = new EscalationDecisionModel();
        $this->assertFalse($model->shouldEscalate);
        $this->assertSame(1.0, $model->confidence);
        $this->assertSame('normal', $model->urgency);
    }

    public function test_escalation_decision_from_array()
    {
        $model = EscalationDecisionModel::fromArray([
            'shouldEscalate' => true,
            'confidence'     => 0.9,
            'reason'         => 'User requested human',
            'urgency'        => 'high',
        ]);

        $this->assertTrue($model->shouldEscalate);
        $this->assertSame('high', $model->urgency);
        $this->assertTrue($model->isValid());
    }

    public function test_voice_response_model_speakable_text()
    {
        $model = VoiceResponseModel::fromArray(['text' => 'Hello world', 'ssml' => '']);
        $this->assertSame('Hello world', $model->getSpeakableText());

        $modelWithSsml = VoiceResponseModel::fromArray([
            'text' => 'Hello',
            'ssml' => '<speak>Hello</speak>',
        ]);
        $this->assertSame('<speak>Hello</speak>', $modelWithSsml->getSpeakableText());
    }

    public function test_data_model_to_array_has_all_keys()
    {
        $model = BookingIntentModel::fromArray(['customerName' => 'Test']);
        $arr   = $model->toArray();

        $this->assertArrayHasKey('isBookingIntent', $arr);
        $this->assertArrayHasKey('customerName', $arr);
        $this->assertArrayHasKey('confidence', $arr);
        $this->assertArrayHasKey('preferredDate', $arr);
    }

    public function test_training_result_model()
    {
        $model = TrainingResultModel::fromArray([
            'success'       => true,
            'chunksCreated' => 5,
            'tokensUsed'    => 1200,
            'sourceType'    => 'text',
        ]);

        $this->assertTrue($model->success);
        $this->assertSame(5, $model->chunksCreated);
        $this->assertTrue($model->isValid());
    }

    public function test_quote_request_model()
    {
        $model = QuoteRequestModel::fromArray([
            'isQuoteRequest' => true,
            'serviceType'    => 'cleaning',
            'propertyType'   => 'house',
        ]);

        $this->assertTrue($model->isQuoteRequest);
        $this->assertSame('cleaning', $model->serviceType);
        $this->assertTrue($model->isValid());
    }
}
