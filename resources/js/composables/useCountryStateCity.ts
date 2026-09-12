// composables/useCountryStateCity.ts
import { ref, watch } from 'vue';
import axios from 'axios';
import { SelectOption } from '@/types';
import { bahamasData } from '@/data/bahamas';

interface StateOption extends SelectOption {
    name: string;
    state_code?: string;
}

export function useCountryStateCity() {
    const countries = ref<SelectOption[]>([]);
    const states = ref<StateOption[]>([]);
    const cities = ref<SelectOption[]>([]);
    const fetchingStates = ref(false);
    const fetchingCities = ref(false);
    const isLoadingCountry = ref(false);
    const isLoadingState = ref(false);
    const isLoadingCity = ref(false);
    const selectedCountry = ref<string>('');
    const selectedState = ref<string>('');

    // Watch for country changes and load states
    watch(selectedCountry, (newCountry) => {
        if (newCountry) {
            fetchStates(newCountry);
        } else {
            resetStatesAndCities();
        }
    });

    // Watch for state changes and load cities
    watch(selectedState, (newState) => {
        if (newState && selectedCountry.value) {
            fetchCities(selectedCountry.value, newState);
        } else {
            resetCities();
        }
    });

    const fetchCountries = async () => {
        try {
            isLoadingCountry.value = true;
            const response = await axios.get('/proxy/countries');
            // Handle the response structure from countriesnow.space API
            const countriesData = response.data.data || response.data;
            countries.value = (Array.isArray(countriesData) ? countriesData : []).map((country: any) => ({
                label: country.name || country.country,
                value: country.iso2,
                iso3: country.iso3,
            }));

        } catch (e) {
            console.error('Failed to load countries', e);
        }
        finally {
            isLoadingCountry.value = false
        }
    };

    const fetchStates = async (countryIso2: string | undefined) => {
        if (!countryIso2) return;
        fetchingStates.value = true;
        isLoadingState.value = true;
        try {
            if (countryIso2 === 'BS') {
                // Use local data exclusively for Bahamas to ensure code consistency
                states.value = bahamasData.map(state => ({
                    label: state.name,
                    value: state.code,
                    name: state.name,
                }));
            } else {
                // Get country name from countries array
                const country = countries.value.find(c => c.value === countryIso2);
                if (!country) return;

                const response = await axios.post('/proxy/states', {
                    country: country.label
                });

                // Handle the response structure from countriesnow.space API
                const statesData = response.data.data?.states || response.data.states || [];
                states.value = (Array.isArray(statesData) ? statesData : []).map((state: any) => ({
                    label: state.name,
                    value: state.name,
                    name: state.name,
                    state_code: state.state_code,

                }));
            }
        } catch (e) {
            console.error('Failed to load states', e);
        } finally {
            fetchingStates.value = false;
            isLoadingState.value = false;
        }
    };

    const fetchCities = async (countryIso2: string | undefined, stateIso2: string | undefined) => {
        if (!countryIso2 || !stateIso2) return;
        fetchingCities.value = true;
        isLoadingCity.value = true;

        try {
            // Handle Bahamas cities manually
            if (countryIso2 === 'BS') {
                console.log('Fetching Bahamas cities for state:', stateIso2);

                const stateData = bahamasData.find(s => s.code === stateIso2);
                const citiesForState = stateData ? stateData.cities.map(city => ({
                    label: city,
                    value: city
                })) : [];

                console.log('Cities found for state:', stateIso2, citiesForState);
                cities.value = citiesForState;
            } else {
                // Get country and state name from countries array
                const country = countries.value.find(c => c.value === countryIso2);
                const state = states.value.find(s => s.value === stateIso2 || s.state_code === stateIso2);
                if (!country) return;

                // Use POST request to countriesnow.space API
                const response = await axios.post('/proxy/cities', {
                    country: country.label,
                    state: state ? state.label : stateIso2
                });

                // Handle the response structure from countriesnow.space API
                const citiesData = response.data.data || [];
                cities.value = (Array.isArray(citiesData) ? citiesData : []).map((city: string) => ({
                    label: city,
                    value: city,
                }));
            }
        } catch (e) {
            console.error('Failed to load cities', e);
        } finally {
            fetchingCities.value = false;
            isLoadingCity.value = false;
        }
    };

    const resetStatesAndCities = () => {
        states.value = [];
        cities.value = [];
    };

    const resetCities = () => {
        cities.value = [];
    };

    return {
        countries,
        states,
        cities,
        fetchingStates,
        fetchingCities,
        isLoadingCountry,
        isLoadingState,
        isLoadingCity,
        selectedCountry,
        selectedState,
        fetchCountries,
        fetchStates,
        fetchCities,
        resetStatesAndCities,
        resetCities,
    };
}
