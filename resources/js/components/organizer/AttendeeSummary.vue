<template>
    <section style="margin-top: 50px;">
        <div class="section-header">
            <h2>Attendees Summary</h2>
            <a href="#" class="section-link">Manage attendees</a>
        </div>
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">4148</div>
                    <div class="summary-card-label">Orders placed</div>
                </div>
            </div>
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">1653</div>
                    <div class="summary-card-label">Paid orders</div>
                </div>
            </div>
            <div class="summary-card" style="background: orange; position: relative;">
                <div style="position: absolute; top: 10px; right: 10px; opacity: 0.5;">🛒</div>
                <div>
                    <div class="summary-card-value">1581</div>
                    <div class="summary-card-label">Awaiting payment</div>
                </div>
            </div>
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">9996</div>
                    <div class="summary-card-label">Tickets sold</div>
                </div>
            </div>
        </div>

        <!-- Line Chart -->
        <div class="chart-container">
            <h3>Tickets sales this month</h3>
            <canvas id="salesChart" height="395" style="display: block; box-sizing: border-box; height: 395px; width: 1187px;" width="1187"></canvas>
        </div>

        <!-- Attendee Orders Table -->
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Organizer / Event / Date / Ticket</th>
                    <th>Attendee / POS</th>
                    <th>Order date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr data-reference="8bfc4930406c80b">
                    <td style="font-family: monospace; color: #00AEEF;">8bfc4930406c80b</td>
                    <td>
                        <div class="event-name">Adventure In The Jungle</div>
                        <div class="event-details">Wed 16 Jul 2025, 5:00 PM EDT › Early bird</div>
                        <div class="ticket-count">1 ticket</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="https://via.placeholder.com/32" alt="" class="attendee-avatar">
                            <div class="attendee-name">Atif Saleem</div>
                        </div>
                    </td>
                    <td class="event-details">Wed 16 Jul 2025, 12:16 AM EDT</td>
                    <td>
                        <span class="status-badge status-awaiting">Awaiting payment</span>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li class="contact-option"><span style="font-size: 0.8em;">📧</span> Contact the attendee</li>
                                <li class="attendee-details-option"><span style="font-size: 0.8em;">📄</span> Details</li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr data-reference="013981c940338a9">
                    <td style="font-family: monospace; color: #00AEEF;">013981c940338a9</td>
                    <td>
                        <div class="event-name">SuperPower</div>
                        <div class="event-details">Mon 28 Jul 2025, 9:00 PM EDT › VIP</div>
                        <div class="ticket-count">1 ticket</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="https://via.placeholder.com/32" alt="" class="attendee-avatar">
                            <div class="attendee-name">John Doe</div>
                        </div>
                    </td>
                    <td class="event-details">Tue 15 Jul 2025, 5:26 PM EDT</td>
                    <td>
                        <span class="status-badge status-canceled">Canceled</span>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li class="contact-option"><span style="font-size: 0.8em;">📧</span> Contact the attendee</li>
                                <li class="attendee-details-option"><span style="font-size: 0.8em;">📄</span> Details</li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr data-reference="f2b567b74b5aaf9">
                    <td style="font-family: monospace; color: #00AEEF;">f2b567b74b5aaf9</td>
                    <td>
                        <div class="event-name">SuperPower</div>
                        <div class="event-details">Mon 28 Jul 2025, 9:00 PM EDT › VIP</div>
                        <div class="ticket-count">1 ticket</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="https://via.placeholder.com/32" alt="" class="attendee-avatar">
                            <div class="attendee-name">John Doe</div>
                        </div>
                    </td>
                    <td class="event-details">Tue 15 Jul 2025, 5:24 PM EDT</td>
                    <td>
                        <span class="status-badge status-paid">Paid</span>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li class="contact-option"><span style="font-size: 0.8em;">📧</span> Contact the attendee</li>
                                <li class="attendee-details-option"><span style="font-size: 0.8em;">📄</span> Details</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
