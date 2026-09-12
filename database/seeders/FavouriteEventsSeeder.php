<?php

namespace Database\Seeders;

use App\Models\Frontend\FavouriteEvent;
use App\Models\LinkUpEvent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FavouriteEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'id' => '0KXcNcFlSndfxt6NX3RT',
                'uid' => 'w4vHGILPZufiWEkcDPTXftGo9B92',
                'eventId' => '0ae0d6ae5d204d99a851',
                'created_at' => 1686307796676023,
            ),
            1 =>
            array(
                'id' => '1rwJMLl7tGIbjThj6ARy',
                'uid' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'eventId' => 'edbd35b80a8c4310bcf2',
                'created_at' => 1689573217401663,
            ),
            2 =>
            array(
                'id' => '38yvej0m4y14TYuJDJ9Y',
                'uid' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'eventId' => '93538e7d09da4c8f9b5f',
                'created_at' => 1684407801779569,
            ),
            3 =>
            array(
                'id' => '4x6aW4giswu1Hrpocjaz',
                'uid' => 'zRLB60U9TXe7AdI55PEOv22jLM03',
                'eventId' => '260c4036ec2643839cf6',
                'created_at' => 1689917504732964,
            ),
            4 =>
            array(
                'id' => '58hPN9XoLCX3TxefvZsO',
                'uid' => 'sApFYTPJFbggDRxzzNGjg7bOtYI3',
                'eventId' => 'fd7917e729d44946bf75',
                'created_at' => 1694111690368432,
            ),
            5 =>
            array(
                'id' => '6bGaDKU4tQwdhHcEGyWw',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => 'e3bf5c125d1442b08074',
                'created_at' => 1683289922736734,
            ),
            6 =>
            array(
                'id' => '76050897bcfd477a8751',
                'eventId' => '4c89ba9652cb4c87999c',
                'uid' => '8dlslfYnrqdulSirM9iws2YNTdb2',
                'created_at' => 1716533362,
            ),
            7 =>
            array(
                'id' => '8fH8V2K8AS9FklxontNG',
                'uid' => 'sApFYTPJFbggDRxzzNGjg7bOtYI3',
                'eventId' => '6757fb6ba6e44798b1e8',
                'created_at' => 1694111689750907,
            ),
            8 =>
            array(
                'id' => '9SnGjFnMqApiyZbHtW5x',
                'uid' => 'sApFYTPJFbggDRxzzNGjg7bOtYI3',
                'eventId' => '2d0ba142f2714a918a30',
                'created_at' => 1694624180319222,
            ),
            9 =>
            array(
                'id' => 'BH5gtnLX5NrZJJnSr25v',
                'uid' => 'Jj57ebWnWORpaYd5evyVg6eCo8B2',
                'eventId' => '0ae0d6ae5d204d99a851',
                'created_at' => 1688488710197946,
            ),
            10 =>
            array(
                'id' => 'BVwJCBtZkriX1NGNXvDx',
                'eventId' => '1c6844fa556b4df7ba67',
                'uid' => 'bESfJw87RGecuTmqVudDHkMTx4l2',
                'created_at' => 1709160232049576,
            ),
            11 =>
            array(
                'id' => 'G3Gqh1kpatuu2lDpOYNh',
                'uid' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'eventId' => '930b1fb7e87f467b9079',
                'created_at' => 1689423813551309,
            ),
            12 =>
            array(
                'id' => 'GO20ypSrxLnXXFNeQnmV',
                'uid' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'eventId' => 'fbd627a6ce3c4887877a',
                'created_at' => 1683291399270217,
            ),
            13 =>
            array(
                'id' => 'HPzMpuBAtCKa11KhnBab',
                'uid' => 'Jj57ebWnWORpaYd5evyVg6eCo8B2',
                'eventId' => '455bca23fcab4f3aa817',
                'created_at' => 1689214556494945,
            ),
            14 =>
            array(
                'id' => 'JIAtrKgRQu7xej4knhI4',
                'uid' => 'yUjUfeY1Z4WCvfMaqMQU3jTJE1W2',
                'eventId' => '08fb724e561342c68639',
                'created_at' => 1690372148828961,
            ),
            15 =>
            array(
                'id' => 'Jsswn85g8VrTtMr0HM8V',
                'uid' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'eventId' => 'bcdbe27372a7492fafaf',
                'created_at' => 1708013904147357,
            ),
            16 =>
            array(
                'id' => 'KLcLk4z933oRd1CIU75c',
                'uid' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'eventId' => '2d0ba142f2714a918a30',
                'created_at' => 1708013900605955,
            ),
            17 =>
            array(
                'id' => 'N6gGYTsU3CVLFxU97k9C',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => 'e737234b6b7044d29956',
                'created_at' => 1683400954087625,
            ),
            18 =>
            array(
                'id' => 'O2zzmaZe2PtKvlGQ4M8o',
                'uid' => 'Lj8Yr6lPv2Xcj2YP9xrLzgzEV0B3',
                'eventId' => 'b4f9b7368737438d87bd',
                'created_at' => 1683680647127443,
            ),
            19 =>
            array(
                'id' => 'PIKUNPEpPUK2mmAwNgtX',
                'uid' => '5sCwEvngUohxegWciwBML2esluF2',
                'eventId' => '1c6844fa556b4df7ba67',
                'created_at' => 1692734112770041,
            ),
            20 =>
            array(
                'id' => 'Q6REUzqKRRGqicJQsbsN',
                'uid' => '5sCwEvngUohxegWciwBML2esluF2',
                'eventId' => 'a1cc15a862f343dea352',
                'created_at' => 1692731871071774,
            ),
            21 =>
            array(
                'id' => 'ZvPZWneXyKejAcXtc3XM',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => 'e3bf5c125d1442b08074',
                'created_at' => 1683289922735817,
            ),
            22 =>
            array(
                'id' => 'a0J2v4HbEu0JRNy247hs',
                'uid' => 'Isqbpix1lxMfGo8Qo5Gl0z2HLI93',
                'eventId' => 'c1cda9e8a7ea4ba9b3af',
                'created_at' => 1690785065920166,
            ),
            23 =>
            array(
                'id' => 'b93e80585a1f4118951d',
                'eventId' => '4c89ba9652cb4c87999c',
                'uid' => '6ySALchvEGMSkJ5nvjEKUW56Fpy2',
                'created_at' => 1716355475,
            ),
            24 =>
            array(
                'id' => 'bYSENtlSB6hftz6cyMY4',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => 'b4f9b7368737438d87bd',
                'created_at' => 1683495324629851,
            ),
            25 =>
            array(
                'id' => 'bb262ad000704a3cad78',
                'eventId' => 'fd7917e729d44946bf75',
                'uid' => 'WcQsl4pTAEZsVSoqJcBost6hVsD3',
                'created_at' => 1716792768,
            ),
            26 =>
            array(
                'id' => 'bb3ebf6c02454c138b35',
                'eventId' => '4c89ba9652cb4c87999c',
                'uid' => 'CRnO6GT7z1fhP5FyTt8kW2lpaU63',
                'created_at' => 1716459521,
            ),
            27 =>
            array(
                'id' => 'bljicyINjklqf7sbstwU',
                'uid' => 'h0nQGDJoVISxNxxssje6gYPaPrd2',
                'eventId' => 'b4f9b7368737438d87bd',
                'created_at' => 1683685110859884,
            ),
            28 =>
            array(
                'id' => 'c48c8bfcf3124ff49abb',
                'eventId' => 'e4eb55c6d74944bfa073',
                'uid' => 'bxjTLg891aUjL9jiQn8JiqnczlI2',
                'created_at' => 1715260763,
            ),
            29 =>
            array(
                'id' => 'fz76pSjUFlZDtJ1zcstP',
                'uid' => 'sApFYTPJFbggDRxzzNGjg7bOtYI3',
                'eventId' => '40c0b22f22a64b99bcc1',
                'created_at' => 1694111698385351,
            ),
            30 =>
            array(
                'id' => 'l5b6zPYSdxkJKmqVJaZV',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => '6f9e2edf16a140d796b5',
                'created_at' => 1683291709195892,
            ),
            31 =>
            array(
                'id' => 'lsN5jHEY9u3zQf22T02j',
                'uid' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'eventId' => '7d5a919013c74ae0b726',
                'created_at' => 1683545357215056,
            ),
            32 =>
            array(
                'id' => 'mQJ5PQ2vdFSDQX69lk7L',
                'uid' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'eventId' => 'a2570c02ca904081843e',
                'created_at' => 1689571509587003,
            ),
            33 =>
            array(
                'id' => 'n570IjhnXIOAlgIq7X30',
                'uid' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'eventId' => '8368f7f1f64a459c91b1',
                'created_at' => 1683272165427989,
            ),
            34 =>
            array(
                'id' => 'nRjVyqxXbaUVVb9Ul5Rc',
                'uid' => 'nOQfj90bfNXqZ8FNdTDNcrSs9cX2',
                'eventId' => 'e88f6b99d3fb4e398bab',
                'created_at' => 1689615000304694,
            ),
            35 =>
            array(
                'id' => 'nUOkTl2gOxy7EFQqHcr9',
                'uid' => '9eIX6E8e4oTLWpMtDu8HTo0k2VG2',
                'eventId' => '1d5b911d8b0f42a89ee8',
                'created_at' => 1691267167785926,
            ),
            36 =>
            array(
                'id' => 'qjkjF3XRRxqnET4Q9x5R',
                'uid' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'eventId' => '5776e5be5fa74879bd17',
                'created_at' => 1688021643742175,
            ),
            37 =>
            array(
                'id' => 'sBVq2VhBLQwWfMu6xzI1',
                'eventId' => '429c215c1d9a4f41a751',
                'uid' => 'bESfJw87RGecuTmqVudDHkMTx4l2',
                'created_at' => 1709160217386655,
            ),
            38 =>
            array(
                'id' => 'sc1W5ScKBFjKAfydJn6i',
                'uid' => 'GKjBfFj5yqWQ7sRyYwPGdNOAmcI2',
                'eventId' => '0ae0d6ae5d204d99a851',
                'created_at' => 1683513587162733,
            ),
            39 =>
            array(
                'id' => 'tWlie8Pbnd58q0j6erNy',
                'eventId' => '2c397b6436a649cbb90b',
                'uid' => 'bESfJw87RGecuTmqVudDHkMTx4l2',
                'created_at' => 1709160237682140,
            ),
            40 =>
            array(
                'id' => 'uKTXPNdqcoyUQOptzONV',
                'uid' => 'yUjUfeY1Z4WCvfMaqMQU3jTJE1W2',
                'eventId' => '1d5b911d8b0f42a89ee8',
                'created_at' => 1690372157746355,
            ),
            41 =>
            array(
                'id' => 'vGejj2G9xTfvHHzvDVxM',
                'uid' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'eventId' => '1c6844fa556b4df7ba67',
                'created_at' => 1718120315160669,
            ),
            42 =>
            array(
                'id' => 'wSFm6iRCLDrznpubIYVu',
                'uid' => '5sCwEvngUohxegWciwBML2esluF2',
                'eventId' => 'fd7917e729d44946bf75',
                'created_at' => 1692732398735590,
            ),
            43 =>
            array(
                'id' => 'xlXn7vO7aNwOjQdfdTf4',
                'uid' => 'Jj57ebWnWORpaYd5evyVg6eCo8B2',
                'eventId' => '0f3ac5a5530b4284af46',
                'created_at' => 1689213809922646,
            ),
            44 =>
            array(
                'id' => 'xoxcbp1oTUMIUmJl52TY',
                'uid' => 'nOQfj90bfNXqZ8FNdTDNcrSs9cX2',
                'eventId' => '0268827e6bbe46789578',
                'created_at' => 1689408522714077,
            ),
            45 =>
            array(
                'id' => 'zYyD4iG2Rk2Us2BZoPVT',
                'uid' => 'skG7hoEfRSMxIZyO34fUhy4yt8R2',
                'eventId' => '429c215c1d9a4f41a751',
                'created_at' => 1692377665274620,
            ),
        );
        foreach ($data as $item) {
            $event = LinkUpEvent::where('firebase_id', $item['eventId'])->first();
            $user = User::where('uid', $item['uid'])->first();
            // Log::info($event);
            // Log::info($user);
            if ($event && $user) {
                FavouriteEvent::updateOrCreate(
                    ['user_id' => $user->id, 'event_id' => $event->id],
                    []
                );
            }
        }
    }
}
