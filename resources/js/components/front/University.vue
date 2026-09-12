<template>
    <div class="form-container">
        <label for="country">Select Country:</label>
        <select id="country" v-model="country" @change="onCountryChange">
            <option value="">-- Select Country --</option>
            <option value="none">I did not attend university</option>
            <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
        </select>

        <div v-if="showState">
            <label for="state">Select State:</label>
            <select id="state" v-model="state" @change="onStateChange">
                <option value="">-- Select State --</option>
                <option v-for="s in states" :key="s" :value="s">{{ s }}</option>
            </select>
        </div>

        <div v-if="showProvince">
            <label for="province">Select Province:</label>
            <select id="province" v-model="province" @change="onProvinceChange">
                <option value="">-- Select Province --</option>
                <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
            </select>
        </div>

        <div v-if="showUniversitySelect">
            <label for="university">Select University:</label>
            <select id="university" v-model="university" @change="enableNextButton">
                <option value="">-- Select University --</option>
                <option v-for="u in filteredUniversities" :key="u.name" :value="u.name">{{ u.name }}</option>
            </select>
        </div>

        <div v-if="showUniversityText">
            <label for="university-text">Enter University Name:</label>
            <input
                type="text"
                id="university-text"
                v-model="universityText"
                placeholder="e.g., Stanford University or 'None' if you did not attend"
                @input="enableNextButton"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';

const emit = defineEmits(['update:modelValue']);

const usStateToAbbr = {
    Alabama: 'AL',
    Alaska: 'AK',
    Arizona: 'AZ',
    Arkansas: 'AR',
    California: 'CA',
    Colorado: 'CO',
    Connecticut: 'CT',
    Delaware: 'DE',
    Florida: 'FL',
    Georgia: 'GA',
    Hawaii: 'HI',
    Idaho: 'ID',
    Illinois: 'IL',
    Indiana: 'IN',
    Iowa: 'IA',
    Kansas: 'KS',
    Kentucky: 'KY',
    Louisiana: 'LA',
    Maine: 'ME',
    Maryland: 'MD',
    Massachusetts: 'MA',
    Michigan: 'MI',
    Minnesota: 'MN',
    Mississippi: 'MS',
    Missouri: 'MO',
    Montana: 'MT',
    Nebraska: 'NE',
    Nevada: 'NV',
    'New Hampshire': 'NH',
    'New Jersey': 'NJ',
    'New Mexico': 'NM',
    'New York': 'NY',
    'North Carolina': 'NC',
    'North Dakota': 'ND',
    Ohio: 'OH',
    Oklahoma: 'OK',
    Oregon: 'OR',
    Pennsylvania: 'PA',
    'Rhode Island': 'RI',
    'South Carolina': 'SC',
    'South Dakota': 'SD',
    Tennessee: 'TN',
    Texas: 'TX',
    Utah: 'UT',
    Vermont: 'VT',
    Virginia: 'VA',
    Washington: 'WA',
    'West Virginia': 'WV',
    Wisconsin: 'WI',
    Wyoming: 'WY',
};

const usAbbrToState = Object.fromEntries(Object.entries(usStateToAbbr).map(([name, abbr]) => [abbr, name]));

function normalizeRegion(value) {
    return (value ?? '').toString().trim().toLowerCase();
}

function getUsStateExtrasKey(stateOrProvince) {
    const input = (stateOrProvince ?? '').toString().trim();
    if (!input) return null;

    if (usStateExtras[input]) return input;

    const fromAbbr = usAbbrToState[input.toUpperCase()];
    if (fromAbbr && usStateExtras[fromAbbr]) return fromAbbr;

    const normalizedInput = normalizeRegion(input);
    const normalizedMatch = Object.keys(usStateExtras).find((k) => normalizeRegion(k) === normalizedInput);
    return normalizedMatch || null;
}

function resolveUsStateTargets(stateOrProvince) {
    const input = (stateOrProvince ?? '').toString().trim();
    if (!input) return { full: '', abbr: '' };

    const maybeAbbr = input.toUpperCase();
    const fromAbbr = usAbbrToState[maybeAbbr];

    if (fromAbbr) {
        return {
            full: normalizeRegion(fromAbbr),
            abbr: normalizeRegion(maybeAbbr),
        };
    }

    return {
        full: normalizeRegion(input),
        abbr: normalizeRegion(usStateToAbbr[input] || ''),
    };
}

const usStateExtras = {
    Alabama: [
        { name: 'University of Alabama' },
        { name: 'Auburn University' },
        { name: 'University of Alabama at Birmingham' },
        { name: 'University of South Alabama' },
        { name: 'Samford University' },
        { name: 'Troy University' },
        { name: 'University of Alabama in Huntsville' },
        { name: 'Jacksonville State University' },
        { name: 'University of North Alabama' },
        { name: 'Alabama State University' },
        { name: 'Tuskegee University' },
        { name: 'Spring Hill College' },
        { name: 'Birmingham-Southern College' },
        { name: 'Faulkner University' },
        { name: 'Huntingdon College' },
        { name: 'Miles College' },
        { name: 'Athens State University' },
        { name: 'University of Montevallo' },
    ],
    Alaska: [
        { name: 'University of Alaska Fairbanks' },
        { name: 'University of Alaska Anchorage' },
        { name: 'University of Alaska Southeast' },
        { name: 'Alaska Pacific University' },
        { name: 'Alaska Bible College' },
        { name: 'Ilisagvik College' },
    ],
    Arizona: [
        { name: 'University of Arizona' },
        { name: 'Arizona State University' },
        { name: 'Northern Arizona University' },
        { name: 'Grand Canyon University' },
        { name: 'Embry-Riddle Aeronautical University-Prescott' },
        { name: 'Arizona Christian University' },
        { name: 'International Baptist College and Seminary' },
        { name: 'Ottawa University-Surprise' },
        { name: 'Prescott College' },
        { name: 'University of Advancing Technology' },
        { name: 'Diné College' },
    ],
    California: [
        { name: 'Stanford University' },
        { name: 'University of California, Berkeley' },
        { name: 'University of California, Los Angeles' },
        { name: 'University of Southern California' },
        { name: 'California Institute of Technology' },
        { name: 'University of California, San Diego' },
        { name: 'University of California, Davis' },
        { name: 'University of California, Santa Barbara' },
        { name: 'University of California, Irvine' },
        { name: 'University of California, Riverside' },
        { name: 'University of California, Santa Cruz' },
        { name: 'University of California, Merced' },
        { name: 'California State University, Long Beach' },
        { name: 'San Diego State University' },
        { name: 'California Polytechnic State University, San Luis Obispo' },
        { name: 'California State University, Fullerton' },
        { name: 'San Jose State University' },
        { name: 'California State University, Northridge' },
        { name: 'San Francisco State University' },
        { name: 'Pepperdine University' },
        { name: 'Loyola Marymount University' },
        { name: 'University of San Diego' },
        { name: 'Santa Clara University' },
        { name: 'University of the Pacific' },
        { name: 'Chapman University' },
        { name: 'Occidental College' },
        { name: 'Pomona College' },
        { name: 'Claremont McKenna College' },
        { name: 'Harvey Mudd College' },
        { name: 'Scripps College' },
        { name: 'Pitzer College' },
        { name: 'University of San Francisco' },
        { name: 'California Institute of the Arts' },
        { name: 'Biola University' },
        { name: 'Azusa Pacific University' },
    ],
    Colorado: [
        { name: 'University of Colorado Boulder' },
        { name: 'Colorado School of Mines' },
        { name: 'University of Denver' },
        { name: 'Colorado State University' },
        { name: 'University of Colorado Denver' },
        { name: 'Regis University' },
        { name: 'University of Northern Colorado' },
        { name: 'Colorado Mesa University' },
        { name: 'Western Colorado University' },
        { name: 'Adams State University' },
        { name: 'Fort Lewis College' },
        { name: 'Colorado Christian University' },
        { name: 'University of Colorado Colorado Springs' },
        { name: 'Metropolitan State University of Denver' },
        { name: 'Colorado State University-Pueblo' },
        { name: 'Naropa University' },
        { name: 'Johnson & Wales University-Denver' },
        { name: 'Rocky Mountain College of Art and Design' },
    ],
    Connecticut: [
        { name: 'Yale University' },
        { name: 'University of Connecticut' },
        { name: 'Fairfield University' },
        { name: 'Quinnipiac University' },
        { name: 'Sacred Heart University' },
        { name: 'University of Hartford' },
        { name: 'Wesleyan University' },
        { name: 'Trinity College' },
        { name: 'Connecticut College' },
        { name: 'Central Connecticut State University' },
        { name: 'Southern Connecticut State University' },
        { name: 'Eastern Connecticut State University' },
        { name: 'Western Connecticut State University' },
        { name: 'University of New Haven' },
        { name: 'Albertus Magnus College' },
        { name: 'Goodwin University' },
        { name: 'Mitchell College' },
        { name: 'Post University' },
        { name: 'University of Bridgeport' },
        { name: 'University of Saint Joseph' },
    ],
    Delaware: [
        { name: 'University of Delaware' },
        { name: 'Delaware State University' },
        { name: 'Goldey-Beacom College' },
        { name: 'Wesley College' },
        { name: 'Wilmington University' },
    ],
    Florida: [
        { name: 'University of Miami' },
        { name: 'Florida Memorial University' },
        { name: 'Florida A&M University' },
        { name: 'Florida State University' },
        { name: 'Florida International University' },
        { name: 'University of Florida' },
        { name: 'University of Central Florida' },
        { name: 'University of South Florida' },
        { name: 'Florida Atlantic University' },
        { name: 'Florida Gulf Coast University' },
        { name: 'University of North Florida' },
        { name: 'University of West Florida' },
        { name: 'Nova Southeastern University' },
        { name: 'Embry-Riddle Aeronautical University' },
        { name: 'Rollins College' },
        { name: 'Stetson University' },
    ],
    Georgia: [
        { name: 'Emory University' },
        { name: 'Georgia Institute of Technology' },
        { name: 'University of Georgia' },
        { name: 'Georgia State University' },
        { name: 'Mercer University' },
        { name: 'Kennesaw State University' },
        { name: 'Georgia Southern University' },
        { name: 'Augusta University' },
        { name: 'Morehouse College' },
        { name: 'Spelman College' },
        { name: 'Clark Atlanta University' },
        { name: 'Savannah College of Art and Design' },
        { name: 'Agnes Scott College' },
        { name: 'Oglethorpe University' },
        { name: 'Piedmont University' },
        { name: 'Berry College' },
        { name: 'Covenant College' },
        { name: 'Brenau University' },
        { name: 'Valdosta State University' },
        { name: 'University of West Georgia' },
        { name: 'Georgia College & State University' },
    ],
    Hawaii: [
        { name: 'University of Hawaii at Manoa' },
        { name: 'University of Hawaii at Hilo' },
        { name: 'Brigham Young University--Hawaii' },
        { name: 'University of Hawaii--West Oahu' },
        { name: 'Chaminade University of Honolulu' },
        { name: 'Hawaii Pacific University' },
        { name: 'Pacific Rim Christian University' },
        { name: 'University of Hawaii Maui College' },
        { name: 'Honolulu Community College' },
        { name: 'Kapiolani Community College' },
        { name: 'Leeward Community College' },
        { name: 'Windward Community College' },
        { name: 'Hawaii Community College' },
        { name: 'Kauai Community College' },
    ],
    Idaho: [
        { name: 'Boise Bible College' },
        { name: 'Boise State University' },
        { name: 'Brigham Young University–Idaho' },
        { name: 'College of Idaho' },
        { name: 'Idaho College of Osteopathic Medicine' },
        { name: 'Idaho State University' },
        { name: 'Lewis–Clark State College' },
        { name: 'New Saint Andrews College' },
        { name: 'Northwest Nazarene University' },
        { name: 'University of Idaho' },
    ],
    Illinois: [
        { name: 'Chicago State University' },
        { name: 'Eastern Illinois University' },
        { name: 'Governors State University' },
        { name: 'Illinois State University' },
        { name: 'University of Illinois Chicago' },
        { name: 'University of Illinois Springfield' },
        { name: 'University of Illinois Urbana-Champaign' },
        { name: 'Northeastern Illinois University' },
        { name: 'Northern Illinois University' },
        { name: 'Southern Illinois University Carbondale' },
        { name: 'Southern Illinois University Edwardsville' },
        { name: 'Western Illinois University' },
        { name: 'Adler University' },
        { name: 'American Islamic College' },
        { name: 'Augustana College' },
        { name: 'Aurora University' },
        { name: 'Benedictine University' },
        { name: 'Blackburn College' },
        { name: 'Bradley University' },
        { name: 'The Chicago School of Professional Psychology' },
        { name: 'University of Chicago' },
        { name: 'Columbia College Chicago' },
        { name: 'Concordia University Chicago' },
        { name: 'DePaul University' },
        { name: 'Dominican University' },
        { name: 'East–West University' },
        { name: 'Elmhurst University' },
        { name: 'Erikson Institute' },
        { name: 'Eureka College' },
        { name: 'Greenville University' },
        { name: 'Illinois College' },
        { name: 'Illinois Institute of Technology' },
        { name: 'Illinois Wesleyan University' },
        { name: 'Judson University' },
        { name: 'Knox College' },
        { name: 'Lake Forest College' },
        { name: 'Lakeview College of Nursing' },
        { name: 'Lewis University' },
        { name: 'Loyola University Chicago' },
        { name: 'Lutheran School of Theology at Chicago' },
        { name: 'McKendree University' },
        { name: 'Methodist College' },
        { name: 'Midwestern University' },
        { name: 'Millikin University' },
        { name: 'Monmouth College' },
        { name: 'Moody Bible Institute' },
        { name: 'National Louis University' },
        { name: 'National University of Health Sciences' },
        { name: 'North Central College' },
        { name: 'North Park University' },
        { name: 'Northern Seminary' },
        { name: 'Northwestern University' },
        { name: 'Olivet Nazarene University' },
        { name: 'Principia College' },
        { name: 'Quincy University' },
        { name: 'Rockford University' },
        { name: 'Roosevelt University' },
        { name: 'Rosalind Franklin University of Medicine and Science' },
        { name: 'Rush University' },
        { name: 'Saint Anthony College of Nursing' },
        { name: 'University of St. Francis' },
        { name: 'Saint Xavier University' },
        { name: 'School of the Art Institute of Chicago' },
        { name: 'Spertus Institute for Jewish Learning and Leadership' },
        { name: 'Toyota Technological Institute at Chicago' },
        { name: 'Trinity Christian College' },
        { name: 'Trinity International University' },
        { name: 'VanderCook College of Music' },
        { name: 'Wheaton College' },
        { name: 'American InterContinental University' },
        { name: 'Chamberlain University' },
        { name: 'DeVry University' },
        { name: 'Fox College' },
        { name: 'Lincoln Tech' },
        { name: 'Midwest College of Oriental Medicine' },
        { name: 'Midwest Technical Institute' },
        { name: 'Pacific College of Health and Science' },
        { name: 'Rasmussen College' },
        { name: 'Rockford Career College' },
        { name: 'Taylor Business Institute' },
    ],
    Indiana: [
        { name: 'American College of Education' },
        { name: 'Anabaptist Mennonite Biblical Seminary' },
        { name: 'Anderson University' },
        { name: 'Ball State University' },
        { name: 'Bethany Theological Seminary' },
        { name: 'Bethel University' },
        { name: 'Bishop Simon Bruté College Seminary' },
        { name: 'Butler University' },
        { name: 'Calumet College of St. Joseph' },
        { name: 'Chamberlain University Indiana' },
        { name: 'Christian Theological Seminary' },
        { name: 'College of Biblical Studies' },
        { name: 'Concordia Theological Seminary' },
        { name: 'DePauw University' },
        { name: 'DeVry University–Indiana' },
        { name: 'Earlham College' },
        { name: 'Franklin College' },
        { name: 'Goshen College' },
        { name: 'Grace College & Seminary' },
        { name: 'Hanover College' },
        { name: 'Holy Cross College' },
        { name: 'Huntington University' },
        { name: 'Indiana Bible College' },
        { name: 'Indiana Institute of Technology' },
        { name: 'Indiana State University' },
        { name: 'Indiana University Bloomington' },
        { name: 'Indiana University Columbus' },
        { name: 'Indiana University East' },
        { name: 'Indiana University Fort Wayne' },
        { name: 'Indiana University Indianapolis' },
        { name: 'Indiana University Kokomo' },
        { name: 'Indiana University Northwest' },
        { name: 'Indiana University South Bend' },
        { name: 'Indiana University Southeast' },
        { name: 'Indiana Wesleyan University' },
        { name: 'Manchester University' },
        { name: 'Marian University' },
        { name: 'Martin University' },
        { name: 'Oakland City University' },
        { name: 'Purdue University' },
        { name: 'Purdue University Fort Wayne' },
        { name: 'Purdue University Global' },
        { name: 'Purdue University Northwest' },
        { name: 'Rose-Hulman Institute of Technology' },
        { name: "Saint Mary's College" },
        { name: 'Saint Mary-of-the-Woods College' },
        { name: 'Saint Meinrad Seminary and School of Theology' },
        { name: 'Salem University–Indianapolis' },
        { name: 'South College–Indianapolis' },
        { name: 'Taylor University' },
        { name: 'Trine University' },
        { name: 'Union Bible College and Academy' },
        { name: 'University of Evansville' },
        { name: 'University of Indianapolis' },
        { name: 'University of Notre Dame' },
        { name: 'University of Saint Francis' },
        { name: 'University of Southern Indiana' },
        { name: 'Valparaiso University' },
        { name: 'Wabash College' },
    ],
    Iowa: [
        { name: 'University of Iowa' },
        { name: 'Iowa State University' },
        { name: 'University of Northern Iowa' },
        { name: 'Allen College' },
        { name: 'Briar Cliff University' },
        { name: 'Buena Vista University' },
        { name: 'Central College' },
        { name: 'Clarke University' },
        { name: 'Coe College' },
        { name: 'Cornell College' },
        { name: 'Des Moines University' },
        { name: 'Divine Word College' },
        { name: 'Dordt University' },
        { name: 'Drake University' },
        { name: 'Emmaus University' },
        { name: 'Faith Baptist Bible College and Theological Seminary' },
        { name: 'Graceland University' },
        { name: 'Grand View University' },
        { name: 'Grinnell College' },
        { name: 'Loras College' },
        { name: 'Luther College' },
        { name: 'Maharishi International University' },
        { name: 'Mercy College of Health Sciences' },
        { name: 'Morningside University' },
        { name: 'Mount Mercy University' },
        { name: 'Northwestern College' },
        { name: 'Palmer College of Chiropractic' },
        { name: 'Simpson College' },
        { name: 'St. Ambrose University' },
        { name: "St. Luke's College" },
        { name: 'University of Dubuque' },
        { name: 'Upper Iowa University' },
        { name: 'Wartburg College' },
        { name: 'Wartburg Theological Seminary' },
        { name: 'William Penn University' },
        { name: 'Waldorf University' },
    ],
    Kansas: [
        { name: 'Washburn University' },
        { name: 'Emporia State University' },
        { name: 'Kansas State University' },
        { name: 'University of Kansas' },
        { name: 'Pittsburg State University' },
        { name: 'Wichita State University' },
        { name: 'Fort Hays State University' },
        { name: 'Haskell Indian Nations University' },
        { name: 'Command and General Staff College' },
        { name: 'Kansas College of Osteopathic Medicine' },
        { name: 'Manhattan Christian College' },
        { name: 'Newman University' },
        { name: 'Friends University' },
        { name: 'Baker University' },
        { name: 'Bethel College' },
        { name: 'Kansas Wesleyan University' },
        { name: 'McPherson College' },
        { name: 'Central Christian College of Kansas' },
        { name: 'Tabor College' },
        { name: 'Southwestern College' },
        { name: 'Benedictine College' },
        { name: 'University of Saint Mary' },
        { name: 'Sterling College' },
        { name: 'Bethany College' },
        { name: 'Cleveland University-Kansas City' },
        { name: 'Barclay College' },
        { name: 'Ottawa University' },
        { name: 'Hesston College' },
        { name: 'MidAmerica Nazarene University' },
    ],
    Kentucky: [
        { name: 'University of Kentucky' },
        { name: 'University of Louisville' },
        { name: 'Western Kentucky University' },
        { name: 'Eastern Kentucky University' },
        { name: 'Northern Kentucky University' },
        { name: 'Murray State University' },
        { name: 'Morehead State University' },
        { name: 'Kentucky State University' },
        { name: 'Asbury University' },
        { name: 'Spalding University' },
        { name: 'Georgetown College' },
        { name: 'Sullivan University' },
        { name: 'Midway University' },
        { name: 'Brescia University' },
        { name: 'Lindsey Wilson College' },
        { name: 'Thomas More University' },
        { name: 'Union College' },
        { name: 'Alice Lloyd College' },
    ],
    Maine: [
        { name: 'University of Maine' },
        { name: 'Unity Environmental University' },
        { name: 'Southern Maine Community College' },
        { name: 'University of New England' },
        { name: 'University of Southern Maine' },
        { name: 'Central Maine Community College' },
        { name: 'Husson University' },
        { name: 'University of Maine at Augusta' },
        { name: 'Eastern Maine Community College' },
        { name: 'Kennebec Valley Community College' },
        { name: 'Colby College' },
        { name: 'York County Community College' },
        { name: 'Bowdoin College' },
        { name: 'Bates College' },
        { name: 'Thomas College' },
        { name: 'University of Maine at Farmington' },
        { name: 'University of Maine at Presque Isle' },
        { name: "Saint Joseph's College of Maine" },
        { name: 'Maine Maritime Academy' },
        { name: 'Northern Maine Community College' },
        { name: 'Washington County Community College' },
        { name: 'University of Maine at Fort Kent' },
        { name: 'Maine College of Art & Design' },
        { name: 'Beal University' },
        { name: 'College of the Atlantic' },
        { name: 'University of Maine at Machias' },
        { name: 'University of Maine School of Law' },
        { name: 'Maine College of Health Professions' },
        { name: 'Institute for Doctoral Studies in the Visual Arts' },
        { name: 'New England Bible College and Seminary' },
        { name: 'The Landing School' },
        { name: 'Maine Media College' },
    ],
    Maryland: [
        { name: 'Bowie State University' },
        { name: 'Coppin State University' },
        { name: 'Frostburg State University' },
        { name: 'Morgan State University' },
        { name: 'Salisbury University' },
        { name: 'St. Mary’s College of Maryland' },
        { name: 'Towson University' },
        { name: 'United States Naval Academy' },
        { name: 'University of Baltimore' },
        { name: 'University of Maryland, Baltimore' },
        { name: 'University of Maryland, Baltimore County' },
        { name: 'University of Maryland, College Park' },
        { name: 'University of Maryland Eastern Shore' },
        { name: 'University of Maryland Global Campus' },
        { name: 'Capitol Technology University' },
        { name: 'Goucher College' },
        { name: 'Hood College' },
        { name: 'Johns Hopkins University' },
        { name: 'Loyola University Maryland' },
        { name: 'Maryland Institute College of Art' },
        { name: 'Maryland University of Integrative Health' },
        { name: 'McDaniel College' },
        { name: "Mount St. Mary's University" },
        { name: 'Notre Dame of Maryland University' },
        { name: "St. John's College" },
        { name: 'Stevenson University' },
        { name: 'Washington Adventist University' },
        { name: 'Washington College' },
        { name: 'Allegany College of Maryland' },
        { name: 'Anne Arundel Community College' },
        { name: 'Baltimore City Community College' },
        { name: 'Carroll Community College' },
        { name: 'Cecil College' },
        { name: 'Chesapeake College' },
        { name: 'College of Southern Maryland' },
        { name: 'Community College of Baltimore County' },
        { name: 'Frederick Community College' },
        { name: 'Garrett College' },
        { name: 'Hagerstown Community College' },
        { name: 'Harford Community College' },
        { name: 'Howard Community College' },
        { name: 'Montgomery College' },
        { name: "Prince George's Community College" },
        { name: 'Wor–Wic Community College' },
        { name: 'Breakthrough Bible College' },
        { name: 'Capital Bible Seminary' },
        { name: "St. Mary's Seminary and University" },
        { name: 'Yeshivas Ner Yisroel' },
        { name: "Yeshiva College of The Nation's Capital" },
    ],
    Massachusetts: [
        { name: 'University of Massachusetts Amherst' },
        { name: 'Harvard University' },
        { name: 'Massachusetts Institute of Technology' },
        { name: 'Boston University' },
        { name: 'Boston College' },
        { name: 'Tufts University' },
        { name: 'Northeastern University' },
        { name: 'Brandeis University' },
        { name: 'Worcester Polytechnic Institute' },
        { name: 'University of Massachusetts Boston' },
        { name: 'University of Massachusetts Lowell' },
        { name: 'University of Massachusetts Dartmouth' },
        { name: 'Smith College' },
        { name: 'Amherst College' },
        { name: 'Wellesley College' },
        { name: 'Williams College' },
        { name: 'Clark University' },
        { name: 'Bentley University' },
        { name: 'Suffolk University' },
        { name: 'Endicott College' },
        { name: 'Emerson College' },
        { name: 'Babson College' },
        { name: 'Simmons University' },
        { name: 'Wheaton College' },
        { name: 'Gordon College' },
        { name: 'Mount Holyoke College' },
        { name: 'Holy Cross' },
        { name: 'University of Massachusetts Chan Medical School' },
        { name: 'Berklee College of Music' },
        { name: 'Franklin W. Olin College of Engineering' },
        { name: 'Merrimack College' },
        { name: 'Stonehill College' },
        { name: 'Lesley University' },
        { name: 'Massachusetts College of Art and Design' },
        { name: 'Curry College' },
        { name: 'Hult International Business School' },
        { name: 'Assumption University' },
        { name: 'Bridgewater State University' },
        { name: 'Salem State University' },
        { name: 'Framingham State University' },
        { name: 'Massachusetts College of Liberal Arts' },
        { name: 'Massachusetts Maritime Academy' },
        { name: 'Westfield State University' },
        { name: 'Worcester State University' },
        { name: 'Fitchburg State University' },
        { name: 'Lasell University' },
        { name: 'Regis College' },
        { name: 'Dean College' },
        { name: 'Fisher College' },
        { name: 'Nichols College' },
        { name: 'Massachusetts College of Pharmacy and Health Sciences' },
        { name: 'Anna Maria College' },
        { name: 'Bay Path University' },
        { name: 'Becker College' },
        { name: 'Benjamin Franklin Institute of Technology' },
        { name: 'Eastern Nazarene College' },
        { name: 'Elms College' },
        { name: 'Emmanuel College' },
        { name: 'Franklin Pierce University' },
        { name: 'Laboure College' },
        { name: 'Montserrat College of Art' },
        { name: 'New England College of Optometry' },
        { name: 'New England Conservatory of Music' },
        { name: 'New England Law Boston' },
        { name: 'Northpoint Bible College' },
        { name: 'Pine Manor College' },
        { name: "Saint John's Seminary" },
        { name: 'William James College' },
    ],
    Michigan: [
        { name: 'University of Michigan' },
        { name: 'Michigan State University' },
        { name: 'Michigan Technological University' },
        { name: 'Wayne State University' },
        { name: 'Western Michigan University' },
        { name: 'Grand Valley State University' },
        { name: 'Central Michigan University' },
        { name: 'Eastern Michigan University' },
        { name: 'Ferris State University' },
        { name: 'Northern Michigan University' },
        { name: 'Oakland University' },
        { name: 'Saginaw Valley State University' },
        { name: 'University of Michigan-Dearborn' },
        { name: 'University of Michigan-Flint' },
        { name: 'Lake Superior State University' },
        { name: 'Calvin University' },
        { name: 'Hope College' },
        { name: 'Kalamazoo College' },
        { name: 'Albion College' },
        { name: 'Alma College' },
        { name: 'Aquinas College' },
        { name: 'Baker College' },
        { name: 'Cleary University' },
        { name: 'Concordia University Ann Arbor' },
        { name: 'Davenport University' },
        { name: 'Finlandia University' },
        { name: 'Kettering University' },
        { name: 'Lawrence Technological University' },
        { name: 'Madonna University' },
        { name: 'Northwood University' },
        { name: 'Rochester University' },
        { name: 'Siena Heights University' },
        { name: 'Spring Arbor University' },
        { name: 'University of Detroit Mercy' },
        { name: 'Adrian College' },
        { name: 'Alpena Community College' },
        { name: 'Andrews University' },
        { name: 'Olivet College' },
    ],
    Minnesota: [
        { name: 'University of Minnesota Twin Cities' },
        { name: 'University of Minnesota Duluth' },
        { name: 'University of Minnesota Crookston' },
        { name: 'University of Minnesota Morris' },
        { name: 'University of Minnesota Rochester' },
        { name: 'Minnesota State University, Mankato' },
        { name: 'Minnesota State University Moorhead' },
        { name: 'Bemidji State University' },
        { name: 'Southwest Minnesota State University' },
        { name: 'St. Cloud State University' },
        { name: 'Winona State University' },
        { name: 'Augsburg University' },
        { name: 'Bethany Lutheran College' },
        { name: 'Bethel University' },
        { name: 'Carleton College' },
        { name: 'College of Saint Benedict' },
        { name: 'Concordia College' },
        { name: 'Concordia University, St. Paul' },
        { name: 'Crown College' },
        { name: 'Dunwoody College of Technology' },
        { name: 'Gustavus Adolphus College' },
        { name: 'Hamline University' },
        { name: 'Macalester College' },
        { name: 'Martin Luther College' },
        { name: 'Minneapolis College of Art and Design' },
        { name: "Saint John's University" },
        { name: "Saint Mary's University of Minnesota" },
        { name: 'St. Catherine University' },
        { name: 'St. Olaf College' },
        { name: 'University of Northwestern – St. Paul' },
        { name: 'University of St. Thomas' },
        { name: 'Oak Hills Christian College' },
        { name: 'North Central University' },
        { name: 'Anoka-Ramsey Community College' },
        { name: 'Anoka Technical College' },
        { name: 'Central Lakes College' },
        { name: 'Century College' },
        { name: 'Dakota County Technical College' },
        { name: 'Fond du Lac Tribal and Community College' },
        { name: 'Hennepin Technical College' },
        { name: 'Hibbing Community College' },
        { name: 'Inver Hills Community College' },
        { name: 'Itasca Community College' },
        { name: 'Lake Superior College' },
        { name: 'Leech Lake Tribal College' },
        { name: 'Mesabi Range College' },
        { name: 'Minneapolis Community and Technical College' },
        { name: 'Minnesota North College' },
        { name: 'Minnesota State College Southeast' },
        { name: 'Minnesota State Community and Technical College' },
        { name: 'Minnesota West Community and Technical College' },
        { name: 'Normandale Community College' },
        { name: 'North Hennepin Community College' },
        { name: 'Northland Community & Technical College' },
        { name: 'Northwest Technical College' },
        { name: 'Pine Technical & Community College' },
        { name: 'Rainy River Community College' },
        { name: 'Red Lake Nation College' },
        { name: 'Ridgewater College' },
        { name: 'Riverland Community College' },
        { name: 'Rochester Community and Technical College' },
        { name: 'Saint Paul College' },
        { name: 'South Central College' },
        { name: 'Vermilion Community College' },
        { name: 'White Earth Tribal and Community College' },
    ],
    Mississippi: [
        { name: 'Alcorn State University' },
        { name: 'Delta State University' },
        { name: 'Jackson State University' },
        { name: 'Mississippi State University' },
        { name: 'Mississippi University for Women' },
        { name: 'Mississippi Valley State University' },
        { name: 'University of Mississippi' },
        { name: 'University of Southern Mississippi' },
        { name: 'Belhaven University' },
        { name: 'Blue Mountain Christian University' },
        { name: 'Millsaps College' },
        { name: 'Mississippi College' },
        { name: 'Rust College' },
        { name: 'Tougaloo College' },
        { name: 'William Carey University' },
        { name: 'Southeastern Baptist College' },
        { name: 'Reformed Theological Seminary' },
        { name: 'Wesley Biblical Seminary' },
        { name: 'Coahoma Community College' },
        { name: 'Copiah-Lincoln Community College' },
        { name: 'East Central Community College' },
        { name: 'East Mississippi Community College' },
        { name: 'Hinds Community College' },
        { name: 'Holmes Community College' },
        { name: 'Itawamba Community College' },
        { name: 'Jones County Junior College' },
        { name: 'Meridian Community College' },
        { name: 'Mississippi Delta Community College' },
        { name: 'Mississippi Gulf Coast Community College' },
        { name: 'Northeast Mississippi Community College' },
        { name: 'Northwest Mississippi Community College' },
        { name: 'Pearl River Community College' },
        { name: 'Southwest Mississippi Community College' },
    ],
    'New Jersey': [
        { name: 'Princeton University' },
        { name: 'Rutgers University' },
        { name: 'Seton Hall University' },
        { name: 'Stevens Institute of Technology' },
        { name: 'The College of New Jersey' },
        { name: 'Monmouth University' },
        { name: 'Fairleigh Dickinson University' },
        { name: 'Rider University' },
        { name: 'Drew University' },
        { name: 'Rowan University' },
        { name: 'New Jersey Institute of Technology' },
        { name: 'Kean University' },
        { name: 'Stockton University' },
        { name: 'Montclair State University' },
        { name: 'Ramapo College of New Jersey' },
        { name: 'William Paterson University' },
        { name: 'Centenary University' },
        { name: "Saint Peter's University" },
        { name: 'Caldwell University' },
        { name: 'Felician University' },
        { name: 'Georgian Court University' },
    ],
    'New York': [
        { name: 'Columbia University' },
        { name: 'New York University' },
        { name: 'Cornell University' },
        { name: 'University of Rochester' },
        { name: 'Stony Brook University' },
        { name: 'Syracuse University' },
        { name: 'Fordham University' },
        { name: 'City College of New York' },
        { name: 'University at Buffalo' },
        { name: 'Binghamton University' },
        { name: 'Rensselaer Polytechnic Institute' },
        { name: 'Vassar College' },
        { name: 'Colgate University' },
        { name: 'Barnard College' },
        { name: 'Hamilton College' },
        { name: 'Bard College' },
        { name: 'Skidmore College' },
        { name: 'Union College' },
        { name: 'Rochester Institute of Technology' },
        { name: 'Pace University' },
        { name: "St. John's University" },
        { name: 'Adelphi University' },
        { name: 'Hofstra University' },
        { name: 'Ithaca College' },
        { name: 'Marist College' },
        { name: 'Sarah Lawrence College' },
        { name: 'Yeshiva University' },
    ],
    'North Carolina': [{ name: 'High Point University' }],
    'North Dakota': [
        { name: 'University of North Dakota' },
        { name: 'North Dakota State University' },
        { name: 'Dickinson State University' },
        { name: 'Mayville State University' },
        { name: 'Minot State University' },
        { name: 'Valley City State University' },
        { name: 'University of Mary' },
        { name: 'Jamestown University' },
    ],
    'Rhode Island': [
        { name: 'Brown University' },
        { name: 'University of Rhode Island' },
        { name: 'Rhode Island College' },
        { name: 'Providence College' },
        { name: 'Bryant University' },
        { name: 'Roger Williams University' },
        { name: 'Salve Regina University' },
        { name: 'Johnson & Wales University' },
        { name: 'Rhode Island School of Design' },
    ],
    'South Carolina': [
        { name: 'University of South Carolina' },
        { name: 'Clemson University' },
        { name: 'College of Charleston' },
        { name: 'Furman University' },
        { name: 'Coastal Carolina University' },
        { name: 'Winthrop University' },
        { name: 'South Carolina State University' },
        { name: 'The Citadel' },
        { name: 'Francis Marion University' },
        { name: 'Lander University' },
        { name: 'Charleston Southern University' },
        { name: 'Presbyterian College' },
        { name: 'Wofford College' },
        { name: 'Bob Jones University' },
        { name: 'Anderson University' },
        { name: 'Benedict College' },
        { name: 'Claflin University' },
        { name: 'Converse University' },
        { name: 'Erskine College' },
        { name: 'Limestone University' },
        { name: 'Newberry College' },
        { name: 'North Greenville University' },
        { name: 'Southern Wesleyan University' },
        { name: 'Voorhees University' },
    ],
    Tennessee: [
        { name: 'Vanderbilt University' },
        { name: 'University of Tennessee' },
        { name: 'University of Tennessee at Chattanooga' },
        { name: 'University of Tennessee Health Science Center' },
        { name: 'Tennessee State University' },
        { name: 'Middle Tennessee State University' },
        { name: 'East Tennessee State University' },
        { name: 'Austin Peay State University' },
        { name: 'Tennessee Tech University' },
        { name: 'Belmont University' },
        { name: 'Lipscomb University' },
        { name: 'Union University' },
        { name: 'Fisk University' },
        { name: 'Sewanee: The University of the South' },
        { name: 'Lee University' },
        { name: 'Carson-Newman University' },
        { name: 'Christian Brothers University' },
        { name: 'King University' },
        { name: 'Lincoln Memorial University' },
        { name: 'Maryville College' },
        { name: 'Southern Adventist University' },
    ],
    Texas: [
        { name: 'University of Texas at Austin' },
        { name: 'Texas A&M University' },
        { name: 'Rice University' },
        { name: 'Baylor University' },
        { name: 'University of Houston' },
        { name: 'Texas Tech University' },
        { name: 'Southern Methodist University' },
        { name: 'University of Texas at Dallas' },
        { name: 'University of North Texas' },
        { name: 'Texas Christian University' },
        { name: 'University of Texas at Arlington' },
        { name: 'Texas State University' },
        { name: 'University of Texas at San Antonio' },
        { name: 'Sam Houston State University' },
        { name: 'Trinity University' },
        { name: "St. Edward's University" },
        { name: 'Abilene Christian University' },
        { name: 'University of Texas at El Paso' },
        { name: 'Stephen F. Austin State University' },
        { name: 'Lamar University' },
        { name: 'Prairie View A&M University' },
        { name: 'Tarleton State University' },
        { name: "Texas Woman's University" },
        { name: 'Angelo State University' },
        { name: 'Midwestern State University' },
    ],
    Wyoming: [{ name: 'University of Wyoming' }, { name: 'Wyoming Catholic College' }],
};

const countries = [
    'Afghanistan',
    'Albania',
    'Algeria',
    'Andorra',
    'Angola',
    'Antigua and Barbuda',
    'Argentina',
    'Armenia',
    'Australia',
    'Austria',
    'Azerbaijan',
    'The Bahamas',
    'Bahrain',
    'Bangladesh',
    'Barbados',
    'Belarus',
    'Belgium',
    'Belize',
    'Benin',
    'Bhutan',
    'Bolivia',
    'Bosnia and Herzegovina',
    'Botswana',
    'Brazil',
    'Brunei',
    'Bulgaria',
    'Burkina Faso',
    'Burundi',
    'Cabo Verde',
    'Cambodia',
    'Cameroon',
    'Canada',
    'Central African Republic',
    'Chad',
    'Chile',
    'China',
    'Colombia',
    'Comoros',
    'Congo',
    'Congo (DRC)',
    'Costa Rica',
    'Croatia',
    'Cuba',
    'Cyprus',
    'Czechia',
    'Denmark',
    'Djibouti',
    'Dominica',
    'Dominican Republic',
    'East Timor',
    'Ecuador',
    'Egypt',
    'El Salvador',
    'Equatorial Guinea',
    'Eritrea',
    'Estonia',
    'Eswatini',
    'Ethiopia',
    'Fiji',
    'Finland',
    'France',
    'Gabon',
    'Gambia',
    'Georgia',
    'Germany',
    'Ghana',
    'Greece',
    'Grenada',
    'Guatemala',
    'Guinea',
    'Guinea-Bissau',
    'Guyana',
    'Haiti',
    'Honduras',
    'Hungary',
    'Iceland',
    'India',
    'Indonesia',
    'Iran',
    'Iraq',
    'Ireland',
    'Italy',
    'Jamaica',
    'Japan',
    'Jordan',
    'Kazakhstan',
    'Kenya',
    'Kiribati',
    'Korea (North)',
    'Korea (South)',
    'Kosovo',
    'Kuwait',
    'Kyrgyzstan',
    'Laos',
    'Latvia',
    'Lebanon',
    'Lesotho',
    'Liberia',
    'Libya',
    'Liechtenstein',
    'Lithuania',
    'Luxembourg',
    'Madagascar',
    'Malawi',
    'Malaysia',
    'Maldives',
    'Mali',
    'Malta',
    'Marshall Islands',
    'Mauritania',
    'Mauritius',
    'Mexico',
    'Micronesia',
    'Moldova',
    'Monaco',
    'Mongolia',
    'Montenegro',
    'Morocco',
    'Mozambique',
    'Myanmar',
    'Namibia',
    'Nauru',
    'Nepal',
    'Netherlands',
    'New Zealand',
    'Nicaragua',
    'Niger',
    'Nigeria',
    'North Macedonia',
    'Norway',
    'Oman',
    'Pakistan',
    'Palau',
    'Panama',
    'Papua New Guinea',
    'Paraguay',
    'Peru',
    'Philippines',
    'Poland',
    'Portugal',
    'Qatar',
    'Romania',
    'Russia',
    'Rwanda',
    'Saint Kitts and Nevis',
    'Saint Lucia',
    'Saint Vincent and the Grenadines',
    'Samoa',
    'San Marino',
    'Sao Tome and Principe',
    'Saudi Arabia',
    'Senegal',
    'Serbia',
    'Seychelles',
    'Sierra Leone',
    'Singapore',
    'Slovakia',
    'Slovenia',
    'Solomon Islands',
    'Somalia',
    'South Africa',
    'South Sudan',
    'Spain',
    'Sri Lanka',
    'Sudan',
    'Suriname',
    'Sweden',
    'Switzerland',
    'Syria',
    'Taiwan',
    'Tajikistan',
    'Tanzania',
    'Thailand',
    'Togo',
    'Tonga',
    'Trinidad and Tobago',
    'Tunisia',
    'Turkey',
    'Turkmenistan',
    'Tuvalu',
    'Uganda',
    'Ukraine',
    'United Arab Emirates',
    'United Kingdom',
    'United States',
    'Uruguay',
    'Uzbekistan',
    'Vanuatu',
    'Vatican City',
    'Venezuela',
    'Vietnam',
    'Yemen',
    'Zambia',
    'Zimbabwe',
];

const states = [
    'Alabama',
    'Alaska',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'Florida',
    'Georgia',
    'Hawaii',
    'Idaho',
    'Illinois',
    'Indiana',
    'Iowa',
    'Kansas',
    'Kentucky',
    'Louisiana',
    'Maine',
    'Maryland',
    'Massachusetts',
    'Michigan',
    'Minnesota',
    'Mississippi',
    'Missouri',
    'Montana',
    'Nebraska',
    'Nevada',
    'New Hampshire',
    'New Jersey',
    'New Mexico',
    'New York',
    'North Carolina',
    'North Dakota',
    'Ohio',
    'Oklahoma',
    'Oregon',
    'Pennsylvania',
    'Rhode Island',
    'South Carolina',
    'South Dakota',
    'Tennessee',
    'Texas',
    'Utah',
    'Vermont',
    'Virginia',
    'Washington',
    'West Virginia',
    'Wisconsin',
    'Wyoming',
];

const provinces = [
    'Alberta',
    'British Columbia',
    'Manitoba',
    'New Brunswick',
    'Newfoundland and Labrador',
    'Nova Scotia',
    'Ontario',
    'Prince Edward Island',
    'Quebec',
    'Saskatchewan',
    'Northwest Territories',
    'Nunavut',
    'Yukon',
];

const caribbeanCountries = [
    'Antigua and Barbuda',
    'The Bahamas',
    'Barbados',
    'Cuba',
    'Dominica',
    'Dominican Republic',
    'Grenada',
    'Haiti',
    'Jamaica',
    'Saint Kitts and Nevis',
    'Saint Lucia',
    'Saint Vincent and the Grenadines',
    'Trinidad and Tobago',
];
const latinAmericanCountries = [
    'Argentina',
    'Bolivia',
    'Brazil',
    'Chile',
    'Colombia',
    'Costa Rica',
    'Cuba',
    'Dominican Republic',
    'Ecuador',
    'El Salvador',
    'Guatemala',
    'Haiti',
    'Honduras',
    'Mexico',
    'Nicaragua',
    'Panama',
    'Paraguay',
    'Peru',
    'Uruguay',
    'Venezuela',
];

const university = defineModel();
const universityText = ref('');

const country = ref('');
const state = ref('');
const province = ref('');
// const university = ref('');

const universitiesData = ref([]);
const filteredUniversities = ref([]);
const showState = computed(() => country.value === 'United States');
const showProvince = computed(() => country.value === 'Canada');
const showUniversitySelect = computed(
    () =>
        caribbeanCountries.includes(country.value) ||
        latinAmericanCountries.includes(country.value) ||
        (country.value === 'United States' && state.value) ||
        (country.value === 'Canada' && province.value),
);
const showUniversityText = computed(() => country.value && !showUniversitySelect.value && country.value !== 'none');
const canProceed = computed(
    () =>
        country.value === 'none' ||
        (showUniversitySelect.value && university.value) ||
        (showUniversityText.value && universityText.value.trim().length > 0),
);

function enableNextButton() {
    // Emit typed university to parent so validation updates
    if (showUniversityText.value && universityText.value.trim()) {
        emit('update:modelValue', universityText.value.trim());
    }
}

onMounted(async () => {
    try {
        const response = await fetch('https://raw.githubusercontent.com/Hipo/university-domains-list/master/world_universities_and_domains.json');
        universitiesData.value = await response.json();
    } catch (error) {
        // fallback: nothing, just allow text input
    }
});

function onCountryChange() {
    state.value = '';
    province.value = '';
    university.value = '';
    universityText.value = '';
    filteredUniversities.value = [];
    if (country.value === 'United States' || country.value === 'Canada') {
        // Wait for state/province selection
        return;
    }
    if (caribbeanCountries.includes(country.value) || latinAmericanCountries.includes(country.value)) {
        populateUniversities(country.value);
    }
}

function onStateChange() {
    university.value = '';
    universityText.value = '';
    filteredUniversities.value = [];
    if (state.value) {
        populateUniversities('United States', state.value);
    }
}

function onProvinceChange() {
    university.value = '';
    universityText.value = '';
    filteredUniversities.value = [];
    if (province.value) {
        populateUniversities('Canada', province.value);
    }
}

function populateUniversities(countryName, stateOrProvince = null) {
    let filtered = universitiesData.value.filter(
        (uni) => uni.country === countryName || (countryName === 'The Bahamas' && uni.country === 'Bahamas'),
    );

    filtered = filtered.filter((uni) => typeof uni?.name === 'string' && uni.name.trim().length > 0);

    if (stateOrProvince) {
        if (countryName === 'United States') {
            const { full: targetFull, abbr: targetAbbr } = resolveUsStateTargets(stateOrProvince);
            filtered = filtered.filter((uni) => {
                const stateProvince = normalizeRegion(uni['state-province']);
                return stateProvince === targetFull || (targetAbbr && stateProvince === targetAbbr);
            });

            if (filtered.length === 0) {
                const extrasKey = getUsStateExtrasKey(stateOrProvince);

                if (!extrasKey) {
                    filtered = universitiesData.value.filter(
                        (uni) => uni.country === countryName || (countryName === 'The Bahamas' && uni.country === 'Bahamas'),
                    );

                    filtered = filtered.filter((uni) => typeof uni?.name === 'string' && uni.name.trim().length > 0);
                }
            }
        } else {
            filtered = filtered.filter((uni) => normalizeRegion(uni['state-province']) === normalizeRegion(stateOrProvince));
        }
    }

    // Custom additions (example for The Bahamas, Florida, etc.)
    if (countryName === 'The Bahamas') {
        const bahamasExtras = [{ name: 'University of the Bahamas' }, { name: 'BTVI' }];
        bahamasExtras.forEach((extra) => {
            if (!filtered.some((uni) => uni.name.toLowerCase() === extra.name.toLowerCase())) {
                filtered.push(extra);
            }
        });
    }

    if (countryName === 'United States' && stateOrProvince) {
        const extrasKey = getUsStateExtrasKey(stateOrProvince);
        const extras = extrasKey ? usStateExtras[extrasKey] : null;

        if (extras) {
            extras.forEach((extra) => {
                if (!filtered.some((uni) => uni.name.toLowerCase() === extra.name.toLowerCase())) {
                    filtered.push(extra);
                }
            });
        }
    }

    filtered = filtered.filter((uni) => typeof uni?.name === 'string' && uni.name.trim().length > 0);
    filtered.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    filteredUniversities.value = filtered;
}

function submit() {
    let selectedUniversity;
    if (country.value === 'none') {
        selectedUniversity = 'None';
    } else {
        selectedUniversity = university.value || universityText.value;
    }
    alert(`Selected University: ${selectedUniversity}`);
}
</script>

<style scoped>
h2 {
    color: #00aaff;
}
select,
input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    background-color: #00aaff;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}
button:disabled {
    background-color: #ccc;
}
.hidden {
    display: none;
}
</style>
