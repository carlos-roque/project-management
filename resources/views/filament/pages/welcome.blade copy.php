<x-layout>
  <x-slot:heading>
    Home Page
  </x-slot:heading>

  @php
    $jsonData = json_decode(file_get_contents(resource_path('data.json')), true);
    $mcButtonData = null;

    // Find the specific MC button you want to display
    foreach ($jsonData as $button) {
      if ($button['Text'] === 'MC') { // Assuming 'Text' property identifies the MC button
        $mcButtonData = $button;
        break;
      }
    }
  @endphp

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        @if ($mcButtonData)
          <button
            class="milestone-button"
            id="button-{{ $mcButtonData['ID'] }}"
            style="background-color: {{ $mcButtonData['Color'] }}; color: {{ $mcButtonData['TextColor'] }};"
          >
            {{ $mcButtonData['Text'] }}
          </button>
        @else
          <p>MC button not found in data.json</p>
        @endif
      </div>
    </div>
  </div>

  <style>
    .milestone-button {
      padding: 10px 20px; /* Adjust padding as needed */
      border: none;
      cursor: pointer;
      text-align: center;
      /* width: 100px; Adjust width as needed */
      /* height: 40px; Adjust height as needed */
      margin: 0 auto;
      display: block; /* Center the button */
    }

    .container {
      margin-top: 20px; /* Add some top margin */
    }
  </style>

</x-layout>