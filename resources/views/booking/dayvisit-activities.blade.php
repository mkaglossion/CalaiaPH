<x-page-layout title="CalaiaPH - Select Day Visit Activities" active-header="daytour">
  {{-- Progress Indicator --}}
  <x-booking-progress
      :steps="[
          ['label' => 'Details', 'status' => 'completed'],
          ['label' => 'Activities', 'status' => 'current'],
          ['label' => 'Payment', 'status' => 'pending'],
          ['label' => 'Confirmation', 'status' => 'pending']
      ]"
  />

  {{-- Activities Selection --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh] bg-[#f5efe7]">
       <x-activities-page
            pretitle="CalaiaPH Day Visit"
            title="Select or Add Activities"
            subtitle="These are optional enhancements to your day visit. Choose from our curated experiences or add your own custom activity."
            :back-route="route('dayvisit.index')"
            :payment-route="route('dayvisit.payment')"
            :store-route="route('dayvisit.storeActivities')"
            activities-endpoint="/api/activities"
            :total-guests="$bookingData['guests'] ?? 1"
            :auto-guests="true"
            :booking-date="$bookingData['booking_date'] ?? null"
        />
  </main>
</x-page-layout>