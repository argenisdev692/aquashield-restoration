<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;
use Ramsey\Uuid\Uuid;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [ 'zone_name' => 'Bathroom', 'zone_type' => 'interior', 'code' => 'BATH', 'description' => 'Bathroom area' ],
            [ 'zone_name' => 'Kitchen', 'zone_type' => 'interior', 'code' => 'KTCH', 'description' => 'Kitchen area' ],
            [ 'zone_name' => 'Bedroom', 'zone_type' => 'interior', 'code' => 'BEDR', 'description' => 'Standard bedroom' ],
            [ 'zone_name' => 'Living Room', 'zone_type' => 'interior', 'code' => 'LVRM', 'description' => 'Main living area' ],
            [ 'zone_name' => 'Dining Room', 'zone_type' => 'interior', 'code' => 'DINR', 'description' => 'Dining area' ],
            [ 'zone_name' => 'Basement', 'zone_type' => 'interior', 'code' => 'BSMT', 'description' => 'Basement area' ],
            [ 'zone_name' => 'Attic', 'zone_type' => 'interior', 'code' => 'ATTC', 'description' => 'Attic space' ],
            [ 'zone_name' => 'Garage', 'zone_type' => 'interior', 'code' => 'GARG', 'description' => 'Garage area' ],
            [ 'zone_name' => 'Laundry Room', 'zone_type' => 'interior', 'code' => 'LDRY', 'description' => 'Laundry area' ],
            [ 'zone_name' => 'Hallway', 'zone_type' => 'interior', 'code' => 'HALL', 'description' => 'Hallway/Corridor' ],
            [ 'zone_name' => 'Closet', 'zone_type' => 'interior', 'code' => 'CLST', 'description' => 'Storage closet' ],
            [ 'zone_name' => 'Office', 'zone_type' => 'interior', 'code' => 'OFFC', 'description' => 'Home office' ],
            [ 'zone_name' => 'Family Room', 'zone_type' => 'interior', 'code' => 'FMRM', 'description' => 'Family room' ],
            [ 'zone_name' => 'Utility Room', 'zone_type' => 'interior', 'code' => 'UTIL', 'description' => 'Utility area' ],
            [ 'zone_name' => 'Foyer/Entryway', 'zone_type' => 'interior', 'code' => 'FOYR', 'description' => 'Main entrance area' ],
            [ 'zone_name' => 'Staircase', 'zone_type' => 'interior', 'code' => 'STRS', 'description' => 'Staircase area' ],
            [ 'zone_name' => 'Crawl Space', 'zone_type' => 'interior', 'code' => 'CRWL', 'description' => 'Crawl space' ],
            [ 'zone_name' => 'Study', 'zone_type' => 'interior', 'code' => 'STDY', 'description' => 'Study room' ],
            [ 'zone_name' => 'Guest Room', 'zone_type' => 'interior', 'code' => 'GRST', 'description' => 'Guest bedroom' ],
            [ 'zone_name' => 'Home Theater', 'zone_type' => 'interior', 'code' => 'THTR', 'description' => 'Home theater room' ],
            [ 'zone_name' => 'Wine Cellar', 'zone_type' => 'interior', 'code' => 'WINE', 'description' => 'Wine storage area' ],
            [ 'zone_name' => 'Gym', 'zone_type' => 'interior', 'code' => 'GYMR', 'description' => 'Home gym' ],
            [ 'zone_name' => 'Workshop', 'zone_type' => 'interior', 'code' => 'WKSP', 'description' => 'Workshop area' ],
            [ 'zone_name' => 'Storage Room', 'zone_type' => 'interior', 'code' => 'STOR', 'description' => 'Storage area' ],
            [ 'zone_name' => 'Sunroom', 'zone_type' => 'interior', 'code' => 'SUNR', 'description' => 'Sunroom/Solarium' ],
            [ 'zone_name' => 'Porch', 'zone_type' => 'exterior', 'code' => 'PRCH', 'description' => 'Porch area' ],
            [ 'zone_name' => 'Patio', 'zone_type' => 'exterior', 'code' => 'PATO', 'description' => 'Patio area' ],
            [ 'zone_name' => 'Deck', 'zone_type' => 'exterior', 'code' => 'DECK', 'description' => 'Deck area' ],
            [ 'zone_name' => 'Exterior Walls', 'zone_type' => 'exterior', 'code' => 'EXTW', 'description' => 'Exterior walls' ],
            [ 'zone_name' => 'Roof', 'zone_type' => 'roof', 'code' => 'ROOF', 'description' => 'Main roof area' ],
            [ 'zone_name' => 'Front Roof', 'zone_type' => 'roof', 'code' => 'FROF', 'description' => 'Front section of roof' ],
            [ 'zone_name' => 'Back Roof', 'zone_type' => 'roof', 'code' => 'BROF', 'description' => 'Back section of roof' ],
            [ 'zone_name' => 'Left Roof', 'zone_type' => 'roof', 'code' => 'LROF', 'description' => 'Left section of roof' ],
            [ 'zone_name' => 'Right Roof', 'zone_type' => 'roof', 'code' => 'RROF', 'description' => 'Right section of roof' ],
            [ 'zone_name' => 'Valley', 'zone_type' => 'roof', 'code' => 'VALY', 'description' => 'Roof valley' ],
            [ 'zone_name' => 'Ridge', 'zone_type' => 'roof', 'code' => 'RIDG', 'description' => 'Roof ridge' ],
            [ 'zone_name' => 'Chimney', 'zone_type' => 'roof', 'code' => 'CHIM', 'description' => 'Chimney area' ],
            [ 'zone_name' => 'Skylight', 'zone_type' => 'roof', 'code' => 'SKYL', 'description' => 'Skylight area' ],
            [ 'zone_name' => 'Gutter', 'zone_type' => 'roof', 'code' => 'GUTR', 'description' => 'Gutter system' ],
        ];

        foreach ($zones as $zone) {
            Zone::create([
                'uuid' => Uuid::uuid4()->toString(),
                'zone_name' => $zone['zone_name'],
                'zone_type' => $zone['zone_type'],
                'code' => $zone['code'],
                'description' => $zone['description'],
                'user_id' => 1,
            ]);
        }
    }
} 