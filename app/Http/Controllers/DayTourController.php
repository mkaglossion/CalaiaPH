<?php

namespace App\Http\Controllers;

use App\Models\DayTourSwiperImage;
use Illuminate\Http\Request;

class DayTourController extends Controller
{
    public function index()
    {
        $swiperImages = DayTourSwiperImage::all();

        $packages = [
            [
                'title' => 'Adventure Package',
                'price' => '₱1,500 per person',
                'image' => 'assets/images/carousel/go-cart2.jpg',
                'features' => [
                    'ATV Ride (30 minutes)',
                    'Nature Trail Walk',
                    'Team Building Games',
                    'Lunch Included',
                    'Guided Farm Tour'
                ],
                'details' => [
                    '30-minute ATV ride through scenic trails',
                    'Guided nature walk with wildlife spotting',
                    'Team building games and challenges',
                    'Buffet lunch with local cuisine',
                    'Comprehensive farm tour',
                    'Safety equipment and guides'
                ],
                'gallery' => [
                    'assets/images/carousel/go-cart2.jpg',
                    'assets/images/carousel/activities-1.jpg',
                    'assets/images/carousel/BYOT-2.jpg',
                    'assets/images/carousel/biking-4.jpg'
                ]
            ],
            [
                'title' => 'Family Fun Package',
                'price' => '₱2,000 per family',
                'image' => 'assets/images/carousel/bonfire-5.jpg',
                'features' => [
                    'Family Games & Activities',
                    'Kids Play Area Access',
                    'Picnic Lunch',
                    'Animal Feeding',
                    'Photo Session'
                ],
                'details' => [
                    'Family-friendly games and activities',
                    'Safe and fun kids play area access',
                    'Delicious picnic lunch for the whole family',
                    'Interactive animal feeding experience',
                    'Professional photo session with digital copies'
                ],
                'gallery' => [
                    'assets/images/carousel/bonfire-5.jpg',
                    'assets/images/carousel/games-grounds4.jpg',
                    'assets/images/carousel/our-farm2.jpg',
                    'assets/images/carousel/Retreat-705.jpg'
                ]
            ],
            [
                'title' => 'Nature Experience',
                'price' => '₱1,200 per person',
                'image' => 'assets/images/carousel/organic.png',
                'features' => [
                    'Guided Nature Walk',
                    'Bird Watching',
                    'Organic Farming Demo',
                    'Healthy Lunch',
                    'Meditation Session'
                ],
                'details' => [
                    'Guided nature walk through scenic trails',
                    'Bird watching with expert guides',
                    'Organic farming demonstration',
                    'Healthy farm-to-table lunch',
                    'Guided meditation session',
                    'Nature photography opportunities'
                ],
                'gallery' => [
                    'assets/images/carousel/organic.png',
                    'assets/images/carousel/our-farm.jpg',
                    'assets/images/carousel/Retreat-880.jpg',
                    'assets/images/carousel/Retreat-900.jpg'
                ]
            ]
        ];

        return view('daytour', compact('packages', 'swiperImages'));
    }
}