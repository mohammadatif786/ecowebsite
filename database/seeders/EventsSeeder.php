<?php

namespace Database\Seeders;

use App\Models\LinkUpEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = array(
            0 =>
            array(
                'firebase_id' => 'PS9xyC1w9GcCsPoJRYNIOOKGoL22',
                'venue' => 'Dundas Centre for the Performing Arts Dundas Centre for the Performing Arts PAB Black Box Nassau, NP',
                'website' => 'www.test.com',
                'image_object' => 'events/The_Settin\'_Up_1692733904.jpg',
                'city' => 'Nassau',
                'description' => '<div class="has-user-generated-content">
<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<h2>A﻿ Year of Bahamian Theatre presents <em>The Settin&#39; Up </em>by James Catalyn.</h2>

<h3>Directed by Jason Evans</h3>

<p>&nbsp;</p>

<p><strong>R﻿ated &quot;A&quot;.</strong></p>
</div>
</div>

<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p><em><strong>P﻿atrons are advised that online sales end 24 hours before showtime.</strong></em></p>

<p><em>I﻿f sales are ended online, you may still purchase tickets, depending on availability, directly from the Box Office.</em></p>

<p><em>C﻿ontact the Box Office at +1 (242) 393-3728 or +1 (242) 394-7179.</em></p>
</div>
</div>
</div>',
                'created_at' => 1692733904000,
                'organizer_image_object' => NULL,
                'title' => 'The Settin\' Up',
                'coupon_visibility' => 'live',
                'category_id' => '449357f6ae6c44ebbd4b',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'email' => 'c@caribmedltd.com',
                'disclaimer' => '<section aria-labelledby="refund-policy-heading" class="event-details__section">
<div class="event-details__section-title">
<h2>Refund Policy</h2>
</div>

<div>Contact the organizer to request a refund.</div>
</section>',
                'country' => 'The Bahamas',
                'organizer_name' => 'Shaholin Rolle',
                'type' => 'private',
                'latitude' => '30.7046',
                'longtitude' => '76.7179',
                'likes_count' => 1,
                'start_time' =>
                array(
                    '_seconds' => 1746118800,
                    '_nanoseconds' => 0,
                ),
                'updated_at' => 1745838873000,
                'end_time' =>
                array(
                    '_seconds' => 1754607600,
                    '_nanoseconds' => 0,
                ),
            ),
            1 =>
            array(
                'firebase_id' => 'htIjWtFqcmd7ma3ztgBMuXl6lc32',
                'venue' => 'Chogogo Dive & Beach Resort Curacao',
                'website' => 'missteenmundial.com',
                'image_object' => 'events/MISS_TEEN_Mundial_1695733055.jpg',
                'latitude' => '12.080616984692902',
                'longtitude' => '-68.8779079376317',
                'description' => '<div class="xdj266r x11i5rnm xat24cr x1mh8g0r x1vvkbs x126k92a">
<div dir="auto" style="text-align: start;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u" dir="auto"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h">From Willemstad, Cura&ccedil;ao <span class="x3nfvp2 x1j61x8r x1fcty0u xdj266r xhhsvwb xat24cr xgzva0m xxymvpz xlup9mm x1kky2od"><img alt="🇨🇼" height="16" referrerpolicy="origin-when-cross-origin" src="https://static.xx.fbcdn.net/images/emoji.php/v9/tf6/1/16/1f1e8_1f1fc.png" width="16" /></span> Don&#39;t miss this September 30 the final of the #9 edition of the most important youth contest in the world the #1, Miss Teen World 2023 by @francisco. chop. tv</span></span></div>
</div>

<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s x126k92a">
<div dir="auto" style="text-align: start;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u" dir="auto"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h">Who will be #9 and become the successor to the beautiful @mariona_juncosa ?</span></span></div>
</div>

<div class="x11i5rnm xat24cr x1mh8g0r x1vvkbs xtlvy1s x126k92a">
<div dir="auto" style="text-align: start;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u" dir="auto"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h">Youtube: Francisco Cortez</span></span></div>

<div dir="auto" style="text-align: start;"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u" dir="auto"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x6prxxf xvq8zen xo1l8bm xzsf02u x1yc453h">Facebook: Miss Teen World</span></span></div>
</div>',
                'created_at' => 1695733056000,
                'organizer_image_object' => NULL,
                'title' => 'MISS TEEN Mundial',
                'coupon_visibility' => 'live',
                'category_id' => '449357f6ae6c44ebbd4b',
                'phone' => '242-3432-3433',
                'organizer_name' => 'Cassius  Stuart',
                'email' => 'Missteen.mundial@aol.com',
                'disclaimer' => '<div class="x1iyjqo2">
<div>
<div class="xyamay9 xqmdsaz x1gan7if x1swvt13">
<div>
<div>
<div class="xieb3on x1gslohp">
<div><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x x4zkp8e x676frb x1lkfr7t x1lbecb7 x1s688f xzsf02u" dir="auto">About MISS TEEN Mundial</span></div>
</div>

<div class="xat24cr">
<div class="x13faqbe x78zum5 xdt5ytf">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 x1nhvcw1 x1qjc9v5 xozqiw3 x1q0g3np xexx8yu xykv574 xbmpl8g x4cne27 xifccgj xs83m0k">
<div class="x9f619 x1n2onr6 x1ja2u2z x78zum5 xdt5ytf x193iq5w xeuugli x1r8uery x1iyjqo2 xs83m0k xamitd3 xsyo7zv x16hj40l x10b6aqq x1yrsyyn">
<div class="xzsf02u x6prxxf xvq8zen x126k92a"><span class="x193iq5w xeuugli x13faqbe x1vvkbs xlh3980 xvmahel x1n0sxbx x1lliihq x1s928wv xhkezso x1gmr53x x1cpjm7i x1fgarty x1943h6x xudqn12 x3x7a5m x6prxxf xvq8zen xo1l8bm xzsf02u" dir="auto">Miss Teen Mundial, is today&#39;s choice for teen beauty pageants directed by Francisco Cortez who has 35 years of national and international experience in the pageant industry.</span></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>',
                'type' => 'private',
                'city' => 'Nassau',
                'state' => 'New Providence',
                'country' => 'The Bahamas',
                'likes_count' => -7,
                'end_time' =>
                array(
                    '_seconds' => 1726700400,
                    '_nanoseconds' => 0,
                ),
                'updated_at' => 1718119635000,
                'start_time' =>
                array(
                    '_seconds' => 1726257600,
                    '_nanoseconds' => 298000000,
                ),
            ),
            2 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Thomas Robinson Stadium Bahamas Games',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Bahamas_Beach_Heritage_Festival_1692731545.jpg',
                'city' => 'Nassau',
                'latitude' => '25.05445516943802',
                'longtitude' => '-77.36033184401225',
                'description' => '<p><strong>Join us to explore and experience local Bahamian culture and international specialties.</strong></p>',
                'created_at' => 1692731545000,
                'organizer_image_object' => NULL,
                'title' => 'Bahamas Beach Heritage Festival',
                'coupon_visibility' => 'live',
                'category_id' => '449357f6ae6c44ebbd4b',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'www.alien@hotmsil.com',
                'disclaimer' => '<section aria-labelledby="refund-policy-heading" class="event-details__section">
<div class="event-details__section-title">
<h2>Refund Policy</h2>
</div>

<div>No Refunds</div>
</section>',
                'type' => 'private',
                'start_time' =>
                array(
                    '_seconds' => 1709838000,
                    '_nanoseconds' => 0,
                ),
                'end_time' =>
                array(
                    '_seconds' => 1710547200,
                    '_nanoseconds' => 0,
                ),
                'likes_count' => 2,
                'updated_at' => 1718120425000,
            ),
            3 =>
            array(
                'firebase_id' => 'htIjWtFqcmd7ma3ztgBMuXl6lc32',
                'venue' => '5 Lakeshoue Close',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Let\'s_Make_it_Snow_1702947711.jpg',
                'city' => 'Nassau',
                'description' => '<p>White House Christmas Party. The party of all partier to end the year. All white attier</p>',
                'created_at' => 1702947712000,
                'organizer_image_object' => NULL,
                'title' => 'Let\'s Make it Snow',
                'coupon_visibility' => 'live',
                'category_id' => 'd950241e93774a499492',
                'phone' => '242-422-6374',
                'state' => 'New Providence',
                'organizer_name' => 'Cassius  Stuart',
                'email' => 'cstuart@caribmedltd.com',
                'disclaimer' => '<p>Event spon sponsore by whitehouse productions.</p>',
                'longtitude' => '-68.87891644816284',
                'latitude' => '12.079064277252819',
                'type' => 'private',
                'start_time' =>
                array(
                    '_seconds' => 1703271600,
                    '_nanoseconds' => 0,
                ),
                'end_time' =>
                array(
                    '_seconds' => 1703318400,
                    '_nanoseconds' => 0,
                ),
                'updated_at' => 1745838921000,
            ),
            4 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Fusion Superplex, Gladstone Road, Nassau,',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Sip_N_Chat:_Mom’s_Day_Out_1692674808.jpg',
                'city' => 'Nassau',
                'latitude' => '25.053520325021715',
                'longtitude' => '-77.39771891333298',
                'description' => '<div class="has-user-generated-content">
<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p><strong>Sip N Chat ; Mom&rsquo;s Day Out</strong></p>

<p>Join us for a fabulous day out at <strong>Fusion Superplex</strong> in <strong>Nassau, The Bahamas</strong>. It&#39;s time for moms to relax, unwind, and have some fun! This <strong>in-person</strong> event is the perfect opportunity to connect with other moms and enjoy some well-deserved &quot;me&quot; time. Sip on your favorite drinks and chat about all things motherhood. We&#39;ve got a cozy and inviting atmosphere waiting for you. Don&#39;t miss out on this chance to recharge and make new friends. Grab your girlfriends and come on down to Fusion Superplex for a memorable Mom&#39;s Day Out!</p>
</div>
</div>
</div>',
                'created_at' => 1692674808000,
                'organizer_image_object' => NULL,
                'title' => 'Sip N Chat: Mom’s Day Out',
                'coupon_visibility' => 'live',
                'category_id' => '449357f6ae6c44ebbd4b',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'werwr@hit.com',
                'disclaimer' => '<p><strong>Sip N Chat; Mom&#39;s Day Out: Grab a drink, relax, and enjoy some quality mom time while we chat about all things motherhood.</strong></p>',
                'likes_count' => 1,
                'start_time' =>
                array(
                    '_seconds' => 1701352800,
                    '_nanoseconds' => 0,
                ),
                'end_time' =>
                array(
                    '_seconds' => 1701363600,
                    '_nanoseconds' => 0,
                ),
                'type' => 'private',
                'updated_at' => 1718119700000,
            ),
            5 =>
            array(
                'firebase_id' => 'htIjWtFqcmd7ma3ztgBMuXl6lc32',
                'venue' => 'sachin',
                'country' => 'India',
                'website' => 'www.abc.com',
                'city' => 'Mohali',
                'latitude' => '36.7783',
                'longtitude' => '76.78987',
                'description' => '<p>In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.</p>',
                'created_at' => 1714994847000,
                'title' => 'sachin',
                'type' => 'private',
                'start_time' =>
                array(
                    '_seconds' => 1714996800,
                    '_nanoseconds' => 0,
                ),
                'coupon_visibility' => 'live',
                'category_id' => '0ba250b13e0f43daac39',
                'phone' => '8219739780',
                'state' => 'Punjab',
                'organizer_name' => 'Cassius  Stuart',
                'email' => 'sachin.zeroit@gmail.com',
                'disclaimer' => '<p>In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.</p>',
                'likes_count' => -7,
                'end_time' =>
                array(
                    '_seconds' => 1719741600,
                    '_nanoseconds' => 0,
                ),
                'image_object' => 'restaurants/sachin_1717495951.jpg',
                'updated_at' => 1717495952000,
                'organizer_image_object' => 'events/sachin_organizer_1717495951.jpg',
            ),
            6 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Downtown Nassau Downtown Polo lounge Nassau, New Providence',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Barbies_On_Da_Bay_-_polo_lounge_1692730897.jpg',
                'city' => 'Nassau',
                'end_time' =>
                array(
                    '_seconds' => 1694214000,
                    '_nanoseconds' => 0,
                ),
                'description' => '<p><strong>A fashion party experience</strong></p>',
                'created_at' => 1692730897000,
                'title' => 'Barbies On Da Bay - polo lounge',
                'start_time' =>
                array(
                    '_seconds' => 1694199600,
                    '_nanoseconds' => 0,
                ),
                'coupon_visibility' => 'live',
                'category_id' => 'd950241e93774a499492',
                'phone' => '242322222',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'c@caribmedltd.com',
                'disclaimer' => '<section aria-labelledby="refund-policy-heading" class="event-details__section">
<div class="event-details__section-title">
<h2>Refund Policy</h2>
</div>

<div>Contact the organizer to request a refund.</div>

<div>Eventbrite&#39;s fee is nonrefundable.</div>
</section>',
                'latitude' => '25.058608319651402',
                'longtitude' => '-77.34313920453135',
                'organizer_image_object' => 'events/Barbies_On_Da_Bay_-_polo_lounge_organizer_1692731079.jpg',
                'likes_count' => 1,
                'updated_at' => 1718119738000,
                'type' => 'private',
            ),
            7 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Believers Faith Outreach Ministries Carmichael Road Nassau, New Providence',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/BAHAMAS_PROPHECY_MASS_HEALING_DELIVERANCE_REVIVAL_1692731847.jpg',
                'city' => 'Nassau',
                'latitude' => '25.014520972540247',
                'longtitude' => '-77.39439656220353',
                'end_time' =>
                array(
                    '_seconds' => 1693004400,
                    '_nanoseconds' => 0,
                ),
                'description' => '<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p>J﻿OIN APOSTLE EDISON AND PROPHETESS MATTIE NOTTAGE FOR THE BAHAMAS PROPHECY HEALING AND DELIVERANCE REVIVAL FRIDAY AUGUST 25TH @ 7:30 PM THROUGH SUNDAY AUGUST 27TH, 2023 @ 10:00 AM.</p>

<p>COME AND RECEIVE YOUR MIRACLE, HEALING, DELIVERANCE AND BREAKTHROUGHS. COME AND EXPERIENCE A MOVE OF GOD LIKE NONE OTHER!!!</p>

<p>Y﻿OU DO NOT WANT TO MISS THIS!!! REGISTER NOW!!!</p>

<ul>
	<li><strong>INTERNATIONAL VISITORS THAT ARE FLYING IN TO THE BAHAMAS FROM AN INTERNATIONAL REGION, PLEASE CLICK THE LINK BELOW AND SUBMIT THE FORM, ONE OF OUR TRAINED PROFESSIONAL IVP AGENTS WILL CONTACT YOU AND PROVIDE YOU WITH MORE INFORMATION:</strong></li>
</ul>

<p><a data-airgap-id="45" href="https://form.jotform.com/210125431642846" rel="nofollow noopener noreferrer" target="_blank">https://form.jotform.com/210125431642846</a></p>

<p>&nbsp;</p>

<p><strong>#﻿PROPHETESS DR. MATTIE NOTTAGE 3 DAYS PROPHECY HEALING DELIVERANCE FIRE REVIVAL SERVICES</strong></p>
</div>
</div>',
                'created_at' => 1692731847000,
                'organizer_image_object' => NULL,
                'title' => 'BAHAMAS PROPHECY MASS HEALING DELIVERANCE REVIVAL',
                'start_time' =>
                array(
                    '_seconds' => 1692990000,
                    '_nanoseconds' => 0,
                ),
                'coupon_visibility' => 'live',
                'category_id' => 'ab71a461e4414ab3bb8e',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'c@caribmedltd.com',
                'disclaimer' => '<div class="event-details__section-title">
<h2>Refund Policy</h2>
</div>

<div>Contact the organizer to request a refund.</div>',
                'likes_count' => 1,
                'updated_at' => 1718119772000,
                'type' => 'private',
            ),
            8 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Location  The Island House Mahogany Hill Western Road Nassau, New Providence',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Oppenheimer_1692732832.jpg',
                'city' => 'Nassau',
                'latitude' => '25.034211220877673',
                'longtitude' => '-77.51109784407738',
                'description' => '<p><strong>The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.</strong></p>

<div class="has-user-generated-content">
<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p>Based on the 2005 biography, the film chronicles the life of J. Robert Oppenheimer, a theoretical physicist who was pivotal in developing the first nuclear weapons as part of the Manhattan Project, and thereby ushering in the Atomic Age.</p>

<p>Please note tickets are not free &ndash; Cost of ticket is listed by ticket type and payment will be collected at the door.</p>

<p>RATED T</p>
</div>
</div>
</div>',
                'created_at' => 1692732832000,
                'organizer_image_object' => NULL,
                'title' => 'Oppenheimer',
                'coupon_visibility' => 'live',
                'category_id' => '449357f6ae6c44ebbd4b',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'www.alien@hotmsil.com',
                'type' => 'private',
                'likes_count' => 1,
                'disclaimer' => '<p>In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.</p>',
                'end_time' =>
                array(
                    '_seconds' => 1750982400,
                    '_nanoseconds' => 0,
                ),
                'start_time' =>
                array(
                    '_seconds' => 1741201200,
                    '_nanoseconds' => 0,
                ),
                'updated_at' => 1745839001000,
            ),
            9 =>
            array(
                'firebase_id' => 'htIjWtFqcmd7ma3ztgBMuXl6lc32',
                'venue' => 'Mohali',
                'country' => 'India',
                'website' => 'www.abc.com',
                'image_object' => 'events/Chandigarh_live!_1692783148.jpg',
                'city' => 'Mohali',
                'latitude' => '36.7783',
                'longtitude' => '76.78987',
                'created_at' => 1692783148000,
                'organizer_image_object' => 'events/Chandigarh_live!_organizer_1692783148.png',
                'title' => 'Chandigarh live!',
                'coupon_visibility' => 'live',
                'category_id' => '0ba250b13e0f43daac39',
                'phone' => '1234567890',
                'state' => 'Punjab',
                'organizer_name' => 'Cassius  Stuart',
                'email' => 'sangeeta.zeroit@gmail.com',
                'type' => 'private',
                'start_time' =>
                array(
                    '_seconds' => 1706893200,
                    '_nanoseconds' => 0,
                ),
                'description' => '<p>In publishing and graphic design, Lorem ipsum (/ˌlɔː.rəm ˈɪp.səm/) is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available. It is also used to temporarily replace text in a process called greeking, which allows designers to consider the form of a webpage or publication, without the meaning of the text influencing the design.</p>',
                'disclaimer' => '<p>In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.</p>',
                'end_time' =>
                array(
                    '_seconds' => 1717200600,
                    '_nanoseconds' => 0,
                ),
                'likes_count' => 1,
                'updated_at' => 1718119842000,
            ),
            10 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Thomas Robinson Stadium Bahamas Games',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/Valiant_Live_In_Concert_-_The_Expression_1692733135.jpg',
                'city' => 'Nassau',
                'latitude' => '25.05519383505193',
                'longtitude' => '-77.3602889286705',
                'end_time' =>
                array(
                    '_seconds' => 1692921600,
                    '_nanoseconds' => 0,
                ),
                'description' => '<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p><strong>Valiant Live In Concert - The Expression</strong></p>

<p>Join us for an electrifying evening of music at <strong>Thomas Robinson Stadium</strong> in <strong>Nassau, The Bahamas</strong>. Get ready to be blown away by the captivating performances of Valiant and their band. This in-person event promises to be an unforgettable experience that will leave you wanting more. So mark your calendars and come prepared to dance, sing along, and have an amazing time with Valiant Live In Concert - The Expression!</p>
</div>
</div>',
                'created_at' => 1692733135000,
                'organizer_image_object' => NULL,
                'title' => 'Valiant Live In Concert - The Expression',
                'type' => 'public',
                'start_time' =>
                array(
                    '_seconds' => 1692997200,
                    '_nanoseconds' => 0,
                ),
                'coupon_visibility' => 'live',
                'category_id' => 'd950241e93774a499492',
                'updated_at' => 1692733135000,
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'c@caribmedltd.com',
                'disclaimer' => '<div>Contact the organizer to request a refund.</div>

<div>Eventbrite&#39;s fee is nonrefundable.</div>',
            ),
            11 =>
            array(
                'firebase_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'venue' => 'Grand Hyatt Baha Mar 1 Baha Mar Boulevard Nassau, New Providence',
                'country' => 'The Bahamas',
                'website' => 'www.test.com',
                'image_object' => 'events/2023_Weichert®_Sellebration_1692732359.jpg',
                'city' => 'Nassau',
                'latitude' => '25.070899886201932',
                'longtitude' => '-77.39663985191204',
                'description' => '<p><strong>It&#39;s the biggest Weichert&reg; networking event of the year! Register today.</strong></p>

<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<h2>2023 Weichert&reg; Sellebration Registration Site</h2>

<p>Welcome to the 2023 Weichert&reg; Sellebration registration site! Our national conference is returning to the beautiful, island paradise of The Bahamas--this time at <strong>Grand Hyatt Baha Mar! </strong>Join hundreds of fellow Weichert&reg; Owners, Brokers, Managers and Agents from across the country for three days of networking, dynamic content and inspiring speakers. Plus, you&rsquo;ll have plenty of time to explore and play! See Sellebration details and registration info below. Check back regularly for updates. We look forward to seeing you!</p>

<p><strong>ATTENTION PRIMARY OWNERS: </strong>Do not purchase a ticket through this site. Please contact your RSM to register. You will still need to book your own hotel room.</p>
</div>
</div>',
                'created_at' => 1692732359000,
                'organizer_image_object' => NULL,
                'title' => '2023 Weichert® Sellebration',
                'coupon_visibility' => 'live',
                'category_id' => 'dc02b6225e7a4d75bcff',
                'phone' => '242-3432-3433',
                'state' => 'New Providence',
                'organizer_name' => 'Abdullah Nazeer',
                'email' => 'www.alien@hotmsil.com',
                'disclaimer' => '<div class="eds-l-mar-vert-6 eds-l-sm-mar-vert-4 eds-text-bm structured-content-rich-text">
<div class="eds-text--left">
<p><a data-airgap-id="44" href="https://www.hyatt.com/en-US/group-booking/NASGH/G-WEIC" rel="nofollow noopener noreferrer" target="_blank">Weichert Hotel Block</a> <strong>T﻿he Grand Hyatt Baha M</strong><strong>ar</strong> is situated on Cable Beach, one of the most breathtaking beaches in The Bahamas, with its white sands and tranquil turquoise waters. Play, recharge, and experience the vibrant culture of the islands in luxury. Enjoy spectacular amenities, including tantalizing cuisine from top-notch restauranteur bars and lounges for gathering, the largest casino in the Caribbean, spa, Jack Nicklaus Signature golf, mini-golf and sports courts, shopping, wildlife sanctuary and animal encounters, plus a 15-acre waterpark. There is something for everyone. Come early, stay later!</p>

<p>For more information about Grand Hyatt Baha Mar, along with a complete list of hotel amenities,&nbsp;<a data-airgap-id="46" href="https://www.hyatt.com/en-US/hotel/thebahamas/grand-hyatt-baha-mar/nasgh?src=adm_sem_crp_chico_crp_ppc_LAC-Bahamas-None-Nassau-GH-NASGH_google_Evergreen2022_e_grand%20hyatt%20baha%20mar&amp;gclid=CjwKCAjw_MqgBhAGEiwAnYOAepXbk1VdVdcGcvv164-MAMrXuzE5hPBTR3Y0l3n5wbtQNa_wMACmWRoC7TMQAvD_BwE" rel="nofollow noopener noreferrer" target="_blank">Click Here.</a></p>

<h2>To Book Your Room:</h2>

<p><strong>Reserve A Room In The Weichert Room Block (located at the Grand Hyatt):</strong></p>

<p>BOOK ONLINE: <a data-airgap-id="47" href="https://www.hyatt.com/en-US/group-booking/NASGH/G-WEIC" rel="nofollow noopener noreferrer" target="_blank">Weichert Booking Link</a>.</p>

<p>Or call 1-800-233-1234 and provide them with Group Code <strong>G-WEIC</strong>. If any of your travel dates fall outside the room block, it is recommended you call to book. Dates outside the block are subject to market rates.</p>

<p>Room Block Rate:&nbsp;$189/night.&nbsp;Room block is limited and is available on a <strong>first-come, first-served </strong>basis. So BOOK EARLY!</p>

<p>*Note: The hotel will charge your credit card 1 night stay + taxes &amp; fees, upon booking, to hold reservation.</p>

<p>YOU MUST REGISTER SEPARATELY TO ATTEND SELLEBRATION. HOTEL ROOM RESERVATION MEANS YOU HAVE A PLACE TO SLEEP. IT DOES NOT = A TICKET TO THE EVENT.</p>
</div>
</div>

<p>&nbsp;</p>',
                'start_time' =>
                array(
                    '_seconds' => 1698746400,
                    '_nanoseconds' => 0,
                ),
                'end_time' =>
                array(
                    '_seconds' => 1698782400,
                    '_nanoseconds' => 0,
                ),
                'type' => 'private',
                'likes_count' => 2,
                'updated_at' => 1718120472000,
            ),
        );

        foreach ($events as $event) {
            $temp_start  = $event['start_time']['_seconds'];
            $temp_end  = $event['end_time']['_seconds'];

            $event['start_time'] = Carbon::createFromTimestamp($temp_start)->format('Y-m-d h:m');
            $event['end_time'] = Carbon::createFromTimestamp($temp_end)->format('Y-m-d h:m');
            $event['created_at'] = Carbon::now();
            $event['updated_at'] = Carbon::now();

            // Handle potential 'slug' mismatch if the column is missing but the model expects it
            if (!isset($event['slug']) && Schema::hasColumn('link_up_events', 'slug')) {
                $event['slug'] = Str::slug($event['title']);
            }

            LinkUpEvent::updateOrCreate(
                ['firebase_id' => $event['firebase_id']],
                $event
            );
        }
    }
}
