<?php

use PHPUnit\Framework\TestCase;
use Protoqol\Quo\Data\QuoPayload;

class QuoPayloadTest extends TestCase
{
    public function test_it_can_be_instantiated_via_make(): void
    {
        $payload = QuoPayload::make('test');
        $this->assertInstanceOf(QuoPayload::class, $payload);
    }

    public function test_to_array_has_correct_structure(): void
    {
        $payload = QuoPayload::make('test_value');
        $array   = $payload->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('meta', $array);
        $this->assertArrayHasKey('language', $array);
        $this->assertEquals('php', $array['language']);

        $meta = $array['meta'];
        $this->assertArrayHasKey('id', $meta);
        $this->assertArrayHasKey('uid', $meta);
        $this->assertArrayHasKey('origin', $meta);
        $this->assertArrayHasKey('sender_origin', $meta);
        $this->assertArrayHasKey('time_epoch_ms', $meta);
        $this->assertArrayHasKey('variable', $meta);
        $this->assertArrayHasKey('stack_trace', $meta);
        $this->assertArrayHasKey('thread_info', $meta);
        $this->assertArrayHasKey('runtime', $meta);
        $this->assertArrayHasKey('cpu_usage', $meta);
        $this->assertArrayHasKey('memory_usage', $meta);
        $this->assertArrayHasKey('caller_function', $meta);
    }

    public function test_to_json_returns_valid_json(): void
    {
        $payload = QuoPayload::make('test_value');
        $json    = $payload->toJson();

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals('php', $decoded['language']);
        $this->assertEquals('test_value', $decoded['meta']['variable']['value']);
    }

    public function test_variable_type_detection(): void
    {
        $payload = QuoPayload::make('string');
        $this->assertEquals('string', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make(123);
        $this->assertEquals('int', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make(true);
        $this->assertEquals('bool', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make(1.23);
        $this->assertEquals('float', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make([]);
        $this->assertEquals('array<>', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make(['a' => 1, 'b' => 'c']);
        $this->assertEquals('array<int, string>', $payload->toArray()['meta']['variable']['var_type']);

        $payload = QuoPayload::make(new stdClass());
        $this->assertEquals('stdClass', $payload->toArray()['meta']['variable']['var_type']);
    }

    public function test_variable_value_formatting(): void
    {
        $payload = QuoPayload::make(['a' => 1]);
        // QuoPayload converts JSON : to => and { } to [ ]
        $this->assertEquals('["a" => 1]', $payload->toArray()['meta']['variable']['value']);

        $payload = QuoPayload::make('simple string');
        $this->assertEquals('simple string', $payload->toArray()['meta']['variable']['value']);
    }

    public function test_variable_name_detection_from_quo_call(): void
    {
        $myTestVar = 'some value';
        $payload   = _quo($myTestVar);

        $this->assertEquals('$myTestVar', $payload->toArray()['meta']['variable']['name']);
    }

    public function test_variable_name_detection_with_index(): void
    {
        $var1 = 'first';
        $var2 = 'second';

        $payload = _quo($var1, $var2);

        $this->assertEquals('$var2', $payload->toArray()['meta']['variable']['name'], 'Should detect $var2 as second argument');
    }

    public function test_is_expression_detection(): void
    {
        $payload = _quo(1 + 1);
        $this->assertTrue($payload->toArray()['meta']['variable']['is_expression']);

        $myVar   = 'test';
        $payload = _quo($myVar);
        $this->assertFalse($payload->toArray()['meta']['variable']['is_expression']);
    }

    public function test_memory_address_for_objects(): void
    {
        $obj     = new stdClass();
        $payload = QuoPayload::make($obj);

        $this->assertEquals(spl_object_hash($obj), $payload->toArray()['meta']['variable']['memory_address']);

        $payload = QuoPayload::make('not an object');
        $this->assertNull($payload->toArray()['meta']['variable']['memory_address']);
    }

    public function test_grouping_hash(): void
    {
        $payload = QuoPayload::make('test', 0, 'custom_hash');
        $this->assertEquals('custom_hash', $payload->toArray()['meta']['variable']['grouping_hash']);
    }
}

/**
 * Helper function to match QuoPayload's expectations.
 */
function _quo(...$args)
{
    $lastIdx = count($args) - 1;
    return QuoPayload::make($args[$lastIdx], $lastIdx);
}
