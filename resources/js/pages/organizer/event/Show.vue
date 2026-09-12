<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 py-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
          <Link :href="route('organizer.event.index')" 
                class="p-2 text-gray-400 hover:text-gray-600 rounded-md transition-colors">
            <Icon name="arrow-left" class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ event?.title }}</h1>
            <p class="text-sm text-gray-500">Event Details</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Link :href="route('organizer.event.edit', event?.id)" 
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            Edit Event
          </Link>
          <Link :href="route('organizer.event.report.attendees', event?.id)" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
            View Attendees
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Event Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Event Info Card -->
          <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="aspect-video w-full bg-gray-200 relative">
              <img v-if="event?.image_object" 
                   :src="`${appURL}${event.image_object}`" 
                   :alt="event?.title"
                   class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <Icon name="image" class="w-16 h-16" />
              </div>
              <div class="absolute top-4 right-4">
                <span :class="[
                  'inline-flex px-3 py-1 rounded-full text-sm font-medium',
                  event?.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                ]">
                  {{ event?.status === 'published' ? 'Published' : 'Draft' }}
                </span>
              </div>
            </div>
            
            <div class="p-6">
              <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ event?.title }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ event?.description || 'No description available.' }}</p>
              </div>

              <div class="grid grid-cols-2 gap-6">
                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Date & Time</h3>
                  <div class="flex items-center text-gray-900">
                    <span>{{ formatDateTime(event?.created_at) }}</span>
                  </div>
                </div>
                
                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Location</h3>
                  <div class="flex items-center text-gray-900">
                    <div class="row">
                      <div class="col-md-12">
                        <strong>Country</strong>
                      </div>
                      <div class="col-md-12">
                        <span> {{ event?.country }}</span>
                      </div>
                    </div>
                    <div class="row mx-3">
                      <div class="col-md-12">
                        <strong>State</strong>
                      </div>
                      <div class="col-md-12">
                        <span> {{ event?.state }}</span>
                      </div>
                    </div>
                    <div class="row mx-3">
                      <div class="col-md-12">
                        <strong>City</strong>
                      </div>
                      <div class="col-md-12">
                        <span> {{ event?.city }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Category</h3>
                  <div class="flex items-center text-gray-900">
                    <span>{{ event?.category?.name || 'Uncategorized' }}</span>
                  </div>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Organizer</h3>
                  <div class="flex items-center text-gray-900">
                    
                    <span>{{ event?.organizer?.organizer_name || 'You' }}</span>
                  </div>
                </div>
              </div>

              <!-- Event Stats -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-4 gap-4">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ ticketStats.total_sold || 0 }}</div>
                    <div class="text-sm text-gray-500">Tickets Sold</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">${{ formatCurrency(ticketStats.revenue || 0) }}</div>
                    <div class="text-sm text-gray-500">Revenue</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ ticketStats.checked_in || 0 }}</div>
                    <div class="text-sm text-gray-500">Checked In</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ ticketStats.pending || 0 }}</div>
                    <div class="text-sm text-gray-500">Pending</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Event Type</dt>
                <dd class="text-sm text-gray-900">{{ event?.event_type || 'Standard Event' }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Max Capacity</dt>
                <dd class="text-sm text-gray-900">{{ event?.max_capacity || 'Unlimited' }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                <dd class="text-sm text-gray-900">{{ formatDate(event?.created_at) }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Last Modified</dt>
                <dd class="text-sm text-gray-900">{{ formatDate(event?.updated_at) }}</dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">Age Restriction</dt>
                <dd class="text-sm text-gray-900">{{ event?.age_restriction || 'All Ages' }}</dd>
              </div>

              <div>
                <dt class="text-sm font-medium text-gray-500">Refund Policy</dt>
                <dd class="text-sm text-gray-900">{{ event?.refund_policy || 'Standard' }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <Link :href="route('organizer.event.report.statistics', event?.id)" 
                    class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <Icon name="bar-chart" class="w-4 h-4 inline mr-2" />
                View Statistics
              </Link>
              
              <Link :href="route('organizer.event.report.attendees', event?.id)" 
                    class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <Icon name="users" class="w-4 h-4 inline mr-2" />
                Manage Attendees
              </Link>

              <button @click="duplicateEvent" 
                      class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <Icon name="copy" class="w-4 h-4 inline mr-2" />
                Duplicate Event
              </button>

              <button @click="toggleEventStatus" 
                      :class="[
                        'block w-full text-center px-4 py-2 rounded-md text-sm font-medium transition-colors',
                        event?.status === 'published' 
                          ? 'bg-yellow-600 hover:bg-yellow-700 text-white' 
                          : 'bg-green-600 hover:bg-green-700 text-white'
                      ]">
                <Icon :name="event?.status === 'published' ? 'eye-off' : 'eye'" class="w-4 h-4 inline mr-2" />
                {{ event?.status === 'published' ? 'Move to Draft' : 'Publish Event' }}
              </button>
            </div>
          </div>

          <!-- Ticket Types -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Types</h3>
            <div v-if="event?.tickets?.length" class="space-y-3">
              <div v-for="ticket in event.tickets" :key="ticket.id" 
                   class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                <div>
                  <div class="font-medium text-gray-900">{{ ticket.name }}</div>
                  <div class="text-sm text-gray-500">${{ ticket.price }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">{{ ticket.sold || 0 }}/{{ ticket.quantity }}</div>
                  <div class="text-xs text-gray-500">Sold</div>
                  <div class="text-xs text-purple-600 font-medium mt-1">{{ ticket.checked_in || 0 }} Checked In</div>
                </div>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 text-center py-4">
              No ticket types configured
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-3">
              <div class="flex items-start space-x-3">
                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                <div>
                  <div class="text-sm font-medium text-gray-900">Event created</div>
                  <div class="text-xs text-gray-500">{{ formatDate(event?.created_at) }}</div>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                <div>
                  <div class="text-sm font-medium text-gray-900">Last updated</div>
                  <div class="text-xs text-gray-500">{{ formatDate(event?.updated_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import Icon from '@/components/ui/Icon.vue';

const props = defineProps<{
  event: any;
  ticketStats: {
    total_sold: number;
    revenue: number;
    checked_in: number;
    pending: number;
  };
  appURL: string;
}>();

// Helper functions
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  }).format(amount);
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString([], {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatDateTime = (dateString: string) => {
  if (!dateString) return 'Date TBA';
  return new Date(dateString).toLocaleDateString([], {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  });
};

// Actions
const toggleEventStatus = () => {
  const newStatus = props.event.status === 'published' ? 'draft' : 'published';
  
  router.patch(route('organizer.event.toggle-status', props.event.id), {
    status: newStatus
  }, {
    onSuccess: () => {
      toast.success(`Event ${newStatus === 'published' ? 'published' : 'moved to draft'} successfully!`);
    },
    onError: () => {
      toast.error('Failed to update event status.');
    }
  });
};

const duplicateEvent = () => {
  router.post(route('organizer.event.duplicate', props.event.id), {}, {
    onSuccess: () => {
      toast.success('Event duplicated successfully!');
    },
    onError: () => {
      toast.error('Failed to duplicate event.');
    }
  });
};
</script>

<style scoped>
/* Custom styles if needed */
</style>