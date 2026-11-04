<?php

namespace Tests\Unit;

use App\Models\HomePageSection;
use Tests\TestCase;

class HomePageSectionExtraDataTest extends TestCase
{
    public function test_it_decodes_double_encoded_extra_data(): void
    {
        $value = json_encode(json_encode(['banners' => [['title' => 'Offer']]]));
        $section = new HomePageSection;
        $section->setRawAttributes(['extra_data' => $value]);

        $this->assertSame(['banners' => [['title' => 'Offer']]], $section->extra_data);
    }

    public function test_it_stores_extra_data_as_single_encoded_json(): void
    {
        $section = new HomePageSection;
        $section->extra_data = ['banners' => [['title' => 'Offer']]];
        $savedValue = json_decode($section->getAttributes()['extra_data'], true);

        $this->assertSame(['banners' => [['title' => 'Offer']]], $savedValue);
    }
}