    public function staycationPayment()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete your staycation booking first.');
        }
        
        // Get selected foods from session - ensure it's really populated
        $selectedFoods = $booking['selected_foods'] ?? session('selected_foods', []);
        $selectedFoods = is_array($selectedFoods) && count($selectedFoods) > 0 ? $selectedFoods : [];
        
        // Get selected activities from session
        $selectedActivities = $booking['selected_activities'] ?? session('selected_activities', []);
        $selectedActivities = is_array($selectedActivities) && count($selectedActivities) > 0 ? $selectedActivities : [];
        
        // Fetch food details ONLY if we have selected foods
        $foods = collect();
        if (!empty($selectedFoods)) {
            $foodIds = array_column($selectedFoods, 'id');
            $foods = \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id');
        }
        
        // Fetch staycations for room names
        $staycations = \App\Models\Staycation::all()->keyBy('id');

        return view('booking.staycation-payment', compact('booking', 'selectedFoods', 'foods', 'staycations', 'selectedActivities'));
    }
