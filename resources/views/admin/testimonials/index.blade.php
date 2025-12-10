@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Testimonials Management</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="testimonials-table">
                    @foreach($testimonials as $testimonial)
                    <tr class="testimonial-row" data-id="{{ $testimonial->id }}">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $testimonial->name }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $testimonial->position }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $testimonial->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($testimonial->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                            @if($testimonial->status === 'pending')
                                <form action="{{ route('admin.testimonials.approve', $testimonial) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                        Approve
                                    </button>
                                </form>
                            @endif
                            <button onclick="deleteTestimonial({{ $testimonial->id }})" class="text-red-600 hover:text-red-900 ml-2">
                                Delete
                            </button>
                            <form id="delete-form-{{ $testimonial->id }}" action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function deleteTestimonial(id) {
        if (confirm('Are you sure you want to delete this testimonial?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

    // Drag and drop functionality for reordering
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('testimonials-table');
        let draggedRow = null;

        // Add drag events to rows
        document.querySelectorAll('.testimonial-row').forEach(row => {
            row.setAttribute('draggable', 'true');

            row.addEventListener('dragstart', function() {
                draggedRow = this;
                this.classList.add('bg-blue-50');
                setTimeout(() => this.classList.add('opacity-50'), 0);
            });

            row.addEventListener('dragend', function() {
                this.classList.remove('bg-blue-50', 'opacity-50');
            });

            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('bg-blue-100');
            });

            row.addEventListener('dragleave', function() {
                this.classList.remove('bg-blue-100');
            });

            row.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('bg-blue-100');

                if (draggedRow !== this) {
                    const rows = Array.from(table.querySelectorAll('.testimonial-row'));
                    const currentIndex = rows.indexOf(this);
                    const draggedIndex = rows.indexOf(draggedRow);

                    if (currentIndex < draggedIndex) {
                        this.parentNode.insertBefore(draggedRow, this);
                    } else {
                        this.parentNode.insertBefore(draggedRow, this.nextSibling);
                    }

                    // Update order in database
                    updateTestimonialOrder();
                }
            });
        });

        function updateTestimonialOrder() {
            const rows = Array.from(table.querySelectorAll('.testimonial-row'));
            const testimonials = rows.map((row, index) => ({
                id: row.getAttribute('data-id'),
                order: index + 1
            }));

            fetch('{{ route("admin.testimonials.updateOrder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ testimonials })
            })
            .then(response => response.json())
            .then(data => console.log(data.message))
            .catch(error => console.error('Error:', error));
        }
    });
</script>
@endsection