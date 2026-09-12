// composables/useCountryFlag.ts (or .js)
import { ref } from 'vue'

export function useCountryFlag() {
    const loading = ref(false)
    const error = ref(null)
    const flagUrl = ref('')
    const countryCode = ref('')
    const countryData = ref(null)

    const fetchFlag = async (countryName: string) => {
        loading.value = true
        error.value = null
        flagUrl.value = ''
        countryCode.value = ''

        try {
            const response = await fetch(`https://restcountries.com/v3.1/name/${countryName}`)
            if (!response.ok) throw new Error('Country not found')

            const data = await response.json()
            const code = data[0]?.cca2?.toLowerCase()

            if (!code) throw new Error('Country code not found')

            countryCode.value = code
            countryData.value = data[0]
            flagUrl.value = `https://countryflagsapi.netlify.app/flag/${code}.svg`


        } catch (err: any) {
            error.value = err.message
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        flagUrl,
        countryCode,
        countryData,
        fetchFlag,
    }
}
