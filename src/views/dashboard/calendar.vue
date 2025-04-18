<v-row>
  <v-col>
    <v-calendar ref="calendar" v-model="focus" :start="startDate" :end="endDate" :max="endDate" color="primary"
      :events="reservations" type="month" :event-color="getEventColor" @click:date="openBookingDialog"
      @click:event="openReservationDialog">
      <!-- This will show only the days of the current month -->
      <template v-slot:day="{ date, outside, events }">
        <div v-if="!outside" class="calendar-day">
          <!-- !outside ensures the day is from the current month -->
          <div class="day-number"></div>
          <v-chip v-for="event in events" :key="event.start" :color="getEventColor(event)" class="ma-1" label>
            <strong>{{ formatEventDetails(event) }}</strong>
          </v-chip>
        </div>
      </template>
    </v-calendar>
  </v-col>
</v-row>

<script>
export default {
  methods: {
    formatEventDetails(event) {
      const time = this.formatTime(event.startTime); // Format the time
      return `${time} ${event.name}`; // Combine time and name
    },
    formatTime(time) {
      if (!time) return '';
      const [hours, minutes] = time.split(':').map(Number);
      const period = hours >= 12 ? 'م' : 'ص'; // Use Arabic labels for AM/PM
      const formattedHours = hours % 12 || 12; // Convert 0 to 12 for 12-hour format
      const formattedMinutes = minutes.toString().padStart(2, '0'); // Ensure minutes are always two digits
      return `${formattedHours}:${formattedMinutes} ${period}`;
    },
  },
};
</script>