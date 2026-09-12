<template>
    <AppLayout>
        <h1>Create a New Event</h1>
        <p class="overview-text">To create your event, start by uploading photos and videos to make it visually
            appealing. Click the plus sign in the media section to select files. For each section below, click the plus
            icon to reveal the fields and enter the required information.</p>
        <div class="form-section">
            <h2>Media</h2>
            <div class="upload-area">
                <button class="add-media-btn">+</button>
                <div class="upload-card">
                    <div class="upload-icon">↑</div>
                    <div class="upload-text">Upload photos and videos</div>
                </div>
            </div>
            <input type="file" class="upload-input" id="event-media" accept="image/*,video/*" multiple>
            <div class="preview-container" id="media-preview"></div>
        </div>
        <div class="form-section">
            <h2>Event Details</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Enter the event name, a detailed description, and select the appropriate
                category to help attendees know what to expect.</p>
            <div class="form-fields">
                <div class="form-group">
                    <label for="event-name">Event Name</label>
                    <input type="text" id="event-name" placeholder="Enter event name" required>
                </div>
                <div class="form-group">
                    <label for="event-description">Description</label>
                    <textarea id="event-description" rows="5" placeholder="Describe your event" required></textarea>
                </div>
                <div class="form-group">
                    <label for="event-category">Category</label>
                    <select id="event-category" required>
                        <option value="">Select Category</option>
                        <option>Music</option>
                        <option>Nightlife</option>
                        <option>Performing &amp; Visual Arts</option>
                        <option>Holidays</option>
                        <option>Dating</option>
                        <option>Hobbies</option>
                        <option>Business</option>
                        <option>Food &amp; Drink</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Audiences</label>
                    <p style="color: #00AEEF; font-size: 0.9em; margin-bottom: 10px;"><span
                            style="margin-right: 5px;">ⓘ</span>Select the audience types that are targeted in your
                        event.</p>
                    <div class="audience-checkboxes" style="display: flex; gap: 15px;">
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox">
                            Adults
                        </label>
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox">
                            Children
                        </label>
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox">
                            Family
                        </label>
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox">
                            Youth
                        </label>
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox">
                            Group
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Attendees</label>
                    <p class="info"><span class="info-icon">ⓘ</span> Show the attendees number and list in the event
                        page</p>
                    <div class="toggle-group">
                        <label><input type="radio" name="attendees" value="hide"> Hide</label>
                        <label><input type="radio" name="attendees" value="show" checked> Show</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Enable reviews</label>
                    <div class="toggle-group">
                        <label><input type="radio" name="reviews" value="enable" checked> Enable</label>
                        <label><input type="radio" name="reviews" value="disable"> Disable</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Does this event date have a seating plan?</label>
                    <div class="toggle-group">
                        <label><input type="radio" name="seating-plan" value="no" checked> No</label>
                        <label><input type="radio" name="seating-plan" value="yes"> Yes</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Venue</label>
                    <select>
                        <option>The Big New York Theater</option>
                        <!-- Add more venue options as needed -->
                    </select>
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Date and Time</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Specify the start and end dates and times for your event to ensure attendees can
                plan accordingly.</p>
            <div class="form-fields">
                <h3>Type of event</h3>
                <div class="event-type-tabs">
                    <div class="event-type-tab active" data-type="single">
                        <span>📅</span> Single event
                        <span>For events that happen once</span>
                        <div class="radio">
                            <div class="radio-inner"></div>
                        </div>
                    </div>
                    <div class="event-type-tab" data-type="recurring">
                        <span>📅</span> Recurring event
                        <span class="new-badge">New</span>
                        <span>For timed entry and multiple days</span>
                        <div class="radio"></div>
                    </div>
                </div>
                <div id="single-event-options">
                    <h3>Date and time</h3>
                    <div class="date-time-layout">
                        <div class="form-group date-input">
                            <label for="event-date">Date</label>
                            <input type="date" id="event-date" value="2025-08-23" required>
                        </div>
                        <div class="form-group time-input">
                            <label for="start-time">Start time</label>
                            <input type="time" id="start-time" value="10:00" required>
                        </div>
                        <div class="form-group time-input">
                            <label for="end-time">End time</label>
                            <input type="time" id="end-time" value="12:00" required>
                        </div>
                    </div>
                </div>
                <div id="recurring-event-options" class="recurring-options">
                    <h3>Recurring Schedule</h3>
                    <div class="form-group">
                        <label for="recurrence-pattern">Recurrence Pattern</label>
                        <select id="recurrence-pattern">
                            <option>Daily</option>
                            <option>Weekly</option>
                            <option>Monthly</option>
                        </select>
                    </div>
                    <div class="date-time-layout">
                        <div class="form-group date-input">
                            <label for="start-date">Start Date</label>
                            <input type="date" id="start-date" value="2025-08-23">
                        </div>
                        <div class="form-group date-input">
                            <label for="end-date">End Date</label>
                            <input type="date" id="end-date">
                        </div>
                    </div>
                </div>
                <a href="#" class="more-options" id="more-options-link">More options</a>
                <div id="more-options-content" class="more-options-content">
                    <div class="form-group">
                        <label>Is there an age restriction?</label>
                        <div class="options-group" id="age-restriction-group">
                            <div class="option-pill selected" data-value="all-ages">All ages allowed</div>
                            <div class="option-pill" data-value="age-restriction">There's an age restriction</div>
                            <div class="option-pill" data-value="guardian-needed">Parent or guardian needed</div>
                        </div>
                    </div>
                    <div class="form-group" id="age-options">
                        <label>What ages are allowed?</label>
                        <div class="options-group">
                            <div class="option-pill" data-value="12+">12+</div>
                            <div class="option-pill" data-value="13+">13+</div>
                            <div class="option-pill" data-value="14+">14+</div>
                            <div class="option-pill" data-value="15+">15+</div>
                            <div class="option-pill" data-value="16+">16+</div>
                            <div class="option-pill" data-value="17+">17+</div>
                            <div class="option-pill" data-value="18+">18+</div>
                            <div class="option-pill" data-value="19+">19+</div>
                            <div class="option-pill" data-value="21+">21+</div>
                        </div>
                        <button class="save-btn">Save</button>
                    </div>
                    <div class="form-group" id="guardian-options">
                        <label>What ages need a parent or guardian?</label>
                        <div class="options-group">
                            <div class="option-pill" data-value="under-14">Under 14</div>
                            <div class="option-pill" data-value="under-16">Under 16</div>
                            <div class="option-pill" data-value="under-18">Under 18</div>
                            <div class="option-pill" data-value="under-21">Under 21</div>
                        </div>
                        <button class="save-btn">Save</button>
                    </div>
                    <div class="form-group">
                        <label>Is there parking at your venue?</label>
                        <div class="options-group">
                            <div class="option-pill" data-value="free-parking">Free parking</div>
                            <div class="option-pill" data-value="paid-parking">Paid parking</div>
                            <div class="option-pill" data-value="no-parking">No parking options</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Additional Options</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide additional details like event type, contact information, and disclaimer.
            </p>
            <div class="form-fields">
                <div class="form-group">
                    <label>Event Type</label>
                    <div class="radio-group">
                        <label><input type="radio" name="event-type" checked> Public Event</label>
                        <label><input type="radio" name="event-type"> Private Event</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="Email" required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" placeholder="Phone">
                </div>

                <div class="form-group">
                    <label>Website</label>
                    <input type="url" placeholder="Website">
                </div>

                <div class="form-group">
                    <label>Country</label>
                    <select required>
                        <option value="">Select Country</option>
                        <option>United States</option>
                        <option>Canada</option>
                        <option>Bahamas</option>
                        <!-- Add more as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>State</label>
                    <select>
                        <option value="">Select State</option>
                        <option>California</option>
                        <option>Ontario</option>
                        <!-- Add more as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>City</label>
                    <select>
                        <option value="">Select City</option>
                        <option>San Francisco</option>
                        <option>Toronto</option>
                        <!-- Add more as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Disclaimer</label>
                    <div class="editor-toolbar">
                        <button title="Bold">B</button>
                        <button title="Italic">I</button>
                        <button title="Underline">U</button>
                        <button title="Strikethrough">S</button>
                        <button title="Indent">»</button>
                        <button title="Outdent">&lt;</button>
                        <button title="Heading 1">H1</button>
                        <button title="Heading 2">H2</button>
                        <button title="Unordered List">•</button>
                        <button title="Ordered List">1.</button>
                        <button title="Superscript">X²</button>
                        <button title="Subscript">X₂</button>
                        <button title="Align Left">←</button>
                        <button title="Align Center">↔</button>
                        <button title="Align Right">→</button>
                        <button title="Font Size">A</button>
                        <button title="Font Family">Ff</button>
                        <button title="Link">🔗</button>
                    </div>
                    <div class="editor-content" contenteditable="true">
                        Enter content here ...
                    </div>
                </div>
                <div class="form-group">
                    <label>Images gallery</label>
                    <p class="info"><span class="info-icon">ⓘ</span> Add other images that represent your event to be
                        displayed as a gallery</p>
                    <button class="add-button"><span style="margin-right: 5px;">+</span>ADD</button>
                    <input type="file" id="gallery-images" accept="image/*" multiple style="display: none;">
                    <div class="preview-container" id="gallery-preview"></div>
                </div>
                <div class="form-group">
                    <label>Artists</label>
                    <p class="info"><span class="info-icon">ⓘ</span> Enter the list of artists that will perform in your
                        event (press Enter after each entry)</p>
                    <input type="text" id="artists-input" class="artists-input" placeholder="">
                    <div class="tags-container" id="artists-tags"></div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Location</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide the venue name, full address, city, and country so attendees know where
                the event is taking place.</p>
            <div class="form-fields">
                <div class="location-tabs">
                    <div class="location-tab active" data-location="venue">📍 Venue</div>
                    <div class="location-tab" data-location="online">🖥️ Online event</div>
                    <div class="location-tab" data-location="tba">📅 To be announced</div>
                </div>
                <div id="venue-options">
                    <div class="location-search form-group">
                        <input type="text" id="location-search" placeholder="Location *" required>
                        <div class="error-message">Location is required</div>
                    </div>
                    <a href="#" class="add-details-link">Add location details</a>
                    <div class="map-container">
                        <div id="map" class="map-placeholder">
                            <div style="height: 100%; width: 100%;">
                                <div style="overflow: hidden;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="reserved-seating">
                        <label>
                            <input type="checkbox"> Reserved seating
                        </label>
                        <p class="reserved-description">Use your venue map to set price tiers for each section and
                            choose whether attendees can pick their seat.</p>
                    </div>
                </div>
                <div id="online-options" style="display: none;">
                    <div class="form-group">
                        <label for="online-link">Online Link</label>
                        <input type="url" id="online-link" placeholder="Enter online event link">
                    </div>
                </div>
                <div id="tba-options" style="display: none;">
                    <p>Location to be announced later.</p>
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Organizer Information and Profile</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide details about the event organizer.</p>
            <div class="form-fields">
                <div class="form-group">
                    <label>Organizer Logo</label>
                    <input type="file" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Event Organizer</label>
                    <select>
                        <option value="">Select Organizer</option>
                        <!-- Add options as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Organizer Image</label>
                    <div class="organizer-image-placeholder">
                        <div class="icon">🖼️</div>
                    </div>
                    <input type="file" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Organizer Name</label>
                    <input type="text" value="Shaholin Rolle">
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Scanner</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Select or add scanners for the event.</p>
            <div class="form-fields">
                <div class="form-group">
                    <label>Scanners</label>
                    <div class="tags-container" id="scanners-tags">
                        <div class="tag">
                            <span>Movies Expert Scanner 1</span>
                            <span class="tag-remove">×</span>
                        </div>
                        <div class="tag">
                            <span>Movies Expert Scanner 1</span>
                            <span class="tag-remove">×</span>
                        </div>
                    </div>
                    <input type="text" id="scanners-input" class="scanners-input"
                        placeholder="Add scanner and press Enter">
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Social Media</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide links to your social media profiles for the event.</p>
            <div class="form-fields">
                <div class="form-group">
                    <label>X (Twitter)</label>
                    <input type="text" placeholder="Enter X link">
                </div>

                <div class="form-group">
                    <label>Instagram</label>
                    <input type="text" placeholder="Enter Instagram link">
                </div>

                <div class="form-group">
                    <label>Facebook</label>
                    <input type="text" placeholder="Enter Facebook link">
                </div>

                <div class="form-group">
                    <label>TikTok</label>
                    <input type="text" placeholder="Enter TikTok link">
                </div>

                <div class="form-group">
                    <label>LinkedIn</label>
                    <input type="text" placeholder="Enter LinkedIn link">
                </div>
            </div>
        </div>
        <div class="form-section">
            <h2>Sponsors</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide information about sponsors and their logos.</p>
            <div class="form-fields">
                <h3>Edit Sponsor</h3>
                <div class="form-group">
                    <label>Selected Event</label>
                    <select>
                        <option value="">Select Event</option>
                        <!-- Add event options as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" value="Heineken Beer">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea rows="3">The beer of the Bahamas</textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sponsor Image</label>
                    <input type="file" accept="image/*">
                </div>

                <button class="save-btn"
                    style="background-color: #FFEB3B; color: #000; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer;">Save</button>
            </div>
        </div>
        <div class="form-section">
            <h2>Coupons</h2>
            <button class="add-section-btn">+</button>
            <p class="section-overview">Provide information about coupons for discounts.</p>
            <div class="form-fields">
                <h3>Edit Coupon</h3>
                <div class="form-group">
                    <label>Selected Event</label>
                    <select>
                        <option value="">Select Event</option>
                        <!-- Add event options as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Code</label>
                    <input type="text" value="Believe">
                </div>

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" value="Cassius &amp; Julien Birthday Bash">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea rows="3">The Best Wine in the Caribbean.</textarea>
                </div>

                <div class="form-group">
                    <label>Discount Type</label>
                    <select>
                        <option>Amount</option>
                        <option>Percentage</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Discount</label>
                    <input type="number" value="24.04" step="0.01">
                </div>

                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" value="2025-12-31">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select>
                        <option>Live</option>
                        <option>Draft</option>
                        <option>Expired</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Coupon Image</label>
                    <input type="file" accept="image/*">
                </div>

                <button class="save-btn"
                    style="background-color: #FFEB3B; color: #000; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer;">Save</button>
            </div>
        </div>
        <button class="submit-btn" id="create-event-btn">Create Event</button>
    </AppLayout>


</template>
<script setup lang="ts">
import AppLayout from '@/layouts/organizer/AppLayout.vue';
// Trigger file input on upload area click
const uploadArea = document.querySelector('.upload-area');
const uploadInput = document.querySelector('#event-media');
const addMediaBtn = document.querySelector('.add-media-btn');
const mediaPreview = document.querySelector('#media-preview');

uploadArea.addEventListener('click', () => {
    uploadInput.click();
});

addMediaBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    uploadInput.click();
});

// Handle file upload preview
uploadInput.addEventListener('change', (e) => {
    mediaPreview.innerHTML = '';
    const files = e.target.files;
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = (event) => {
            const elem = file.type.startsWith('image/') ? document.createElement('img') : document.createElement('video');
            elem.src = event.target.result;
            elem.classList.add('preview-item');
            if (elem.tagName === 'VIDEO') {
                elem.controls = true;
            }
            mediaPreview.appendChild(elem);
        };
        reader.readAsDataURL(file);
    }
});

// Handle section plus buttons
const addSectionBtns = document.querySelectorAll('.add-section-btn');
addSectionBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const section = btn.closest('.form-section');
        const fields = section.querySelector('.form-fields');
        const overview = section.querySelector('.section-overview');
        if (fields) {
            fields.style.display = 'block';
        }
        if (overview) {
            overview.style.display = 'none';
        }
        btn.style.display = 'none';
    });
});

// Event type tab switching
const eventTypeTabs = document.querySelectorAll('.event-type-tab');
eventTypeTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        eventTypeTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const type = tab.dataset.type;
        document.getElementById('single-event-options').style.display = type === 'single' ? 'block' : 'none';
        document.getElementById('recurring-event-options').style.display = type === 'recurring' ? 'block' : 'none';
    });
});

// Location tab switching
const locationTabs = document.querySelectorAll('.location-tab');
locationTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        locationTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const location = tab.dataset.location;
        document.getElementById('venue-options').style.display = location === 'venue' ? 'block' : 'none';
        document.getElementById('online-options').style.display = location === 'online' ? 'block' : 'none';
        document.getElementById('tba-options').style.display = location === 'tba' ? 'block' : 'none';
    });
});

// Basic form validation on submit
const createBtn = document.getElementById('create-event-btn');
createBtn.addEventListener('click', (e) => {
    e.preventDefault();
    let valid = true;
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('invalid');
            const error = field.closest('.form-group').querySelector('.error-message') || field.closest('.location-search').querySelector('.error-message');
            if (error) error.style.display = 'block';
            valid = false;
        } else {
            field.classList.remove('invalid');
            const error = field.closest('.form-group').querySelector('.error-message') || field.closest('.location-search').querySelector('.error-message');
            if (error) error.style.display = 'none';
        }
    });
    if (valid) {
        alert('Event created successfully!');
        // Add actual submission logic here
    } else {
        alert('Please fill all required fields.');
    }
});

// Simple rich text editor functionality (basic example)
const editorToolbar = document.querySelector('.editor-toolbar');
const editorContent = document.querySelector('.editor-content');
if (editorToolbar) {
    editorToolbar.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON') {
            const command = e.target.title.toLowerCase().replace(' ', '');
            if (command === 'bold') document.execCommand('bold');
            if (command === 'italic') document.execCommand('italic');
            if (command === 'underline') document.execCommand('underline');
            if (command === 'strikethrough') document.execCommand('strikeThrough');
            if (command === 'indent') document.execCommand('indent');
            if (command === 'outdent') document.execCommand('outdent');
            if (command === 'heading1') document.execCommand('formatBlock', false, 'h1');
            if (command === 'heading2') document.execCommand('formatBlock', false, 'h2');
            if (command === 'unorderedlist') document.execCommand('insertUnorderedList');
            if (command === 'orderedlist') document.execCommand('insertOrderedList');
            if (command === 'superscript') document.execCommand('superscript');
            if (command === 'subscript') document.execCommand('subscript');
            if (command === 'alignleft') document.execCommand('justifyLeft');
            if (command === 'aligncenter') document.execCommand('justifyCenter');
            if (command === 'alignright') document.execCommand('justifyRight');
            if (command === 'link') {
                const url = prompt('Enter URL:');
                if (url) document.execCommand('createLink', false, url);
            }
            // Add more commands as needed
        }
    });
}

// Load Google Maps (replace with your API key)
function initMap() {
    const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 37.7749, lng: -122.4194 },
        zoom: 13
    });
}

// New JS for Images gallery
const addGalleryBtn = document.querySelector('.add-button');
const galleryInput = document.querySelector('#gallery-images');
const galleryPreview = document.querySelector('#gallery-preview');

addGalleryBtn.addEventListener('click', () => {
    galleryInput.click();
});

galleryInput.addEventListener('change', (e) => {
    const files = e.target.files;
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.classList.add('preview-item');
            galleryPreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

// New JS for Artists tags
const artistsInput = document.querySelector('#artists-input');
const artistsTags = document.querySelector('#artists-tags');

artistsInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && artistsInput.value.trim() !== '') {
        e.preventDefault();
        const tag = document.createElement('div');
        tag.classList.add('tag');
        tag.innerHTML = `
                    <span>${artistsInput.value.trim()}</span>
                    <span class="tag-remove">×</span>
                `;
        tag.querySelector('.tag-remove').addEventListener('click', () => {
            tag.remove();
        });
        artistsTags.appendChild(tag);
        artistsInput.value = '';
    }
});

// New JS for Scanners tags
const scannersInput = document.querySelector('#scanners-input');
const scannersTags = document.querySelector('#scanners-tags');

if (scannersInput) {
    scannersInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && scannersInput.value.trim() !== '') {
            e.preventDefault();
            const tag = document.createElement('div');
            tag.classList.add('tag');
            tag.innerHTML = `
                        <span>${scannersInput.value.trim()}</span>
                        <span class="tag-remove">×</span>
                    `;
            tag.querySelector('.tag-remove').addEventListener('click', () => {
                tag.remove();
            });
            scannersTags.appendChild(tag);
            scannersInput.value = '';
        }
    });

    // Add default tag
    const defaultTag = document.createElement('div');
    defaultTag.classList.add('tag');
    defaultTag.innerHTML = `
                <span>Movies Expert Scanner 1</span>
                <span class="tag-remove">×</span>
            `;
    defaultTag.querySelector('.tag-remove').addEventListener('click', () => {
        defaultTag.remove();
    });
    scannersTags.appendChild(defaultTag);
}

// JS for More options expand
const moreOptionsLink = document.getElementById('more-options-link');
const moreOptionsContent = document.getElementById('more-options-content');

moreOptionsLink.addEventListener('click', (e) => {
    e.preventDefault();
    moreOptionsContent.style.display = moreOptionsContent.style.display === 'none' ? 'block' : 'none';
});

// JS for selecting option pills
const optionPills = document.querySelectorAll('.option-pill');
optionPills.forEach(pill => {
    pill.addEventListener('click', () => {
        const group = pill.parentElement;
        group.querySelectorAll('.option-pill').forEach(p => p.classList.remove('selected'));
        pill.classList.add('selected');
        if (pill.dataset.value === 'age-restriction') {
            document.getElementById('age-options').style.display = 'block';
            document.getElementById('guardian-options').style.display = 'none';
        } else if (pill.dataset.value === 'guardian-needed') {
            document.getElementById('age-options').style.display = 'none';
            document.getElementById('guardian-options').style.display = 'block';
        } else {
            document.getElementById('age-options').style.display = 'none';
            document.getElementById('guardian-options').style.display = 'none';
        }
    });
});

// JS for save buttons (for demonstration, just alerts)
const saveBtns = document.querySelectorAll('.save-btn');
saveBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        alert('Options saved!');
    });
});
</script>
