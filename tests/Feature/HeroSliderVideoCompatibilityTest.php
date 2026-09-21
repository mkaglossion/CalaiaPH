<?php

namespace Tests\Feature;

use App\Helpers\ImageHelper;
use Tests\TestCase;

class HeroSliderVideoCompatibilityTest extends TestCase
{
    public function test_cloudinary_video_uses_h264_mp4_and_a_poster_fallback(): void
    {
        $original = 'https://res.cloudinary.com/demo/video/upload/v123/hero.mov';

        $html = view('components.hero-slider', [
            'images' => [(object) [
                'image_path' => $original,
                'title' => 'Welcome',
            ]],
            'texts' => [],
        ])->render();

        $this->assertStringContainsString('autoplay', $html);
        $this->assertStringContainsString('muted', $html);
        $this->assertStringContainsString('playsinline', $html);
        $this->assertStringContainsString('webkit-playsinline', $html);
        $this->assertStringContainsString('poster="https://res.cloudinary.com/demo/video/upload/so_0,w_1280/v123/hero.jpg"', $html);
        $this->assertStringContainsString('<source src="https://res.cloudinary.com/demo/video/upload/f_mp4,vc_h264,q_auto/v123/hero.mp4" type="video/mp4">', $html);
        $this->assertStringContainsString('playPromise.catch(showPoster)', $html);
    }

    public function test_non_cloudinary_video_url_is_left_unchanged(): void
    {
        $video = 'https://cdn.example.com/hero.mp4';

        $this->assertSame($video, ImageHelper::videoMp4($video));
    }
}
