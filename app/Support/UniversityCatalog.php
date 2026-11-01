<?php

namespace App\Support;

/**
 * The starting list of U Vibe campuses (57), the same set as the U Vibe reference design.
 * Keys are stable identifiers shared with the phone app. Members can add more campuses
 * themselves (stored as custom rows in the universities table).
 *
 * Each row: [key, short name, full name, country, flag]
 */
final class UniversityCatalog
{
    public static function rows(): array
    {
        return [
            // Original U Vibe campuses
            ['uwimona', 'UWI Mona', 'The University of the West Indies, Mona', 'Jamaica', '🇯🇲'],
            ['uwicave', 'UWI Cave Hill', 'The University of the West Indies, Cave Hill', 'Barbados', '🇧🇧'],
            ['uwistaug', 'UWI St. Augustine', 'The University of the West Indies, St. Augustine', 'Trinidad & Tobago', '🇹🇹'],
            ['uob', 'Univ. of The Bahamas', 'University of The Bahamas', 'Bahamas', '🇧🇸'],
            ['utech', 'UTech Jamaica', 'University of Technology, Jamaica', 'Jamaica', '🇯🇲'],
            ['uog', 'Univ. of Guyana', 'University of Guyana', 'Guyana', '🇬🇾'],
            ['upr', 'Univ. of Puerto Rico', 'University of Puerto Rico', 'Puerto Rico', '🇵🇷'],
            ['fiu', 'FIU Caribbean SA', 'FIU Caribbean Students Association', 'United States', '🇺🇸'],
            ['howard', 'Howard CSA', 'Howard University Caribbean Students Association', 'United States', '🇺🇸'],
            // More campuses
            ['ncu', 'NCU', 'Northern Caribbean University', 'Jamaica', '🇯🇲'],
            ['cmu', 'CMU', 'Caribbean Maritime University', 'Jamaica', '🇯🇲'],
            ['mico', 'The Mico', 'The Mico University College', 'Jamaica', '🇯🇲'],
            ['edna', 'Edna Manley', 'Edna Manley College of the Visual & Performing Arts', 'Jamaica', '🇯🇲'],
            ['btvi', 'BTVI', 'Bahamas Technical & Vocational Institute', 'Bahamas', '🇧🇸'],
            ['utt', 'UTT', 'University of Trinidad and Tobago', 'Trinidad & Tobago', '🇹🇹'],
            ['usc', 'USC Trinidad', 'University of the Southern Caribbean', 'Trinidad & Tobago', '🇹🇹'],
            ['costaatt', 'COSTAATT', 'College of Science, Technology & Applied Arts of T&T', 'Trinidad & Tobago', '🇹🇹'],
            ['bcc', 'BCC', 'Barbados Community College', 'Barbados', '🇧🇧'],
            ['ubelize', 'Univ. of Belize', 'University of Belize', 'Belize', '🇧🇿'],
            ['galen', 'Galen', 'Galen University', 'Belize', '🇧🇿'],
            ['ueh', 'UEH', "Université d'État d'Haïti", 'Haiti', '🇭🇹'],
            ['quisqueya', 'Quisqueya', 'Université Quisqueya', 'Haiti', '🇭🇹'],
            ['uasd', 'UASD', 'Universidad Autónoma de Santo Domingo', 'Dominican Republic', '🇩🇴'],
            ['pucmm', 'PUCMM', 'Pontificia Universidad Católica Madre y Maestra', 'Dominican Republic', '🇩🇴'],
            ['intec', 'INTEC', 'Instituto Tecnológico de Santo Domingo', 'Dominican Republic', '🇩🇴'],
            ['uhabana', 'Univ. de La Habana', 'Universidad de La Habana', 'Cuba', '🇨🇺'],
            ['interpr', 'Inter PR', 'Inter American University of Puerto Rico', 'Puerto Rico', '🇵🇷'],
            ['salcc', 'SALCC', 'Sir Arthur Lewis Community College', 'Saint Lucia', '🇱🇨'],
            ['sgu', 'SGU', "St. George's University", 'Grenada', '🇬🇩'],
            ['tamcc', 'TAMCC', 'T.A. Marryshow Community College', 'Grenada', '🇬🇩'],
            ['uwifive', 'UWI Five Islands', 'UWI Five Islands', 'Antigua & Barbuda', '🇦🇬'],
            ['cfbc', 'CFBC', 'Clarence Fitzroy Bryant College', 'St. Kitts & Nevis', '🇰🇳'],
            ['svgcc', 'SVGCC', 'St. Vincent & the Grenadines Community College', 'St. Vincent & the Grenadines', '🇻🇨'],
            ['dsc', 'DSC', 'Dominica State College', 'Dominica', '🇩🇲'],
            ['ucci', 'UCCI', 'University College of the Cayman Islands', 'Cayman Islands', '🇰🇾'],
            ['adek', 'AdeKUS', 'Anton de Kom University of Suriname', 'Suriname', '🇸🇷'],
            ['uoc', 'Univ. of Curaçao', 'University of Curaçao', 'Curaçao', '🇨🇼'],
            ['uvi', 'UVI', 'University of the Virgin Islands', 'U.S. Virgin Islands', '🇻🇮'],
            ['bermuda', 'Bermuda College', 'Bermuda College', 'Bermuda', '🇧🇲'],
            ['tcicc', 'TCICC', 'Turks & Caicos Islands Community College', 'Turks & Caicos', '🇹🇨'],
            ['acc', 'ACC', 'Anguilla Community College', 'Anguilla', '🇦🇮'],
            ['unam', 'UNAM', 'Universidad Nacional Autónoma de México', 'Mexico', '🇲🇽'],
            ['tec', 'Tec de Monterrey', 'Tecnológico de Monterrey', 'Mexico', '🇲🇽'],
            ['uniandes', 'Uniandes', 'Universidad de los Andes', 'Colombia', '🇨🇴'],
            ['unal', 'UNAL', 'Universidad Nacional de Colombia', 'Colombia', '🇨🇴'],
            ['usp', 'USP', 'Universidade de São Paulo', 'Brazil', '🇧🇷'],
            ['uba', 'UBA', 'Universidad de Buenos Aires', 'Argentina', '🇦🇷'],
            ['uchile', 'U. de Chile', 'Universidad de Chile', 'Chile', '🇨🇱'],
            ['upanama', 'U. de Panamá', 'Universidad de Panamá', 'Panama', '🇵🇦'],
            ['ucr', 'UCR', 'Universidad de Costa Rica', 'Costa Rica', '🇨🇷'],
            ['ucv', 'UCV', 'Universidad Central de Venezuela', 'Venezuela', '🇻🇪'],
            ['umiami', 'Univ. of Miami', 'University of Miami', 'United States', '🇺🇸'],
            ['famu', 'FAMU', 'Florida A&M University', 'United States', '🇺🇸'],
            ['nsu', 'Nova Southeastern', 'Nova Southeastern University', 'United States', '🇺🇸'],
            ['uoft', 'U of T', 'University of Toronto', 'Canada', '🇨🇦'],
            ['yorku', 'York U', 'York University', 'Canada', '🇨🇦'],
            ['kcl', "King's College London", "King's College London", 'United Kingdom', '🇬🇧'],
        ];
    }
}
