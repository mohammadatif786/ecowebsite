<?php

namespace Database\Seeders;

use App\Models\FlaggedUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlaggedUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'id' => '0h7Et6Xv1klRrumfjWUe',
                'toId' => 'evIrPrMcG8VYSExcyFNHjQfbeOI3',
                'toLastName' => '',
                'toFirstName' => '',
                'fromFirstName' => 'Nji',
                'message' => 'Underage User',
                'fromId' => 'llCsSSQH3BZWh8WRaxMPoZCNgdi2',
                'fromLastName' => 'Mic',
                'timestamp' => 1706136653737,
            ),
            1 =>
            array(
                'id' => '4RTPDcKeGtG0kW6jtfIZ',
                'toId' => 'h56V4AAO0pSTi5JaMd26WpC9Nb23',
                'toLastName' => 'Sarah',
                'toFirstName' => 'DR',
                'fromFirstName' => 'folorunsho',
                'message' => 'Nudity or Sexual Content',
                'fromId' => '8YtUy8qCOddMVAjoBDX9W0f57JI2',
                'fromLastName' => 'oloruntoba',
                'timestamp' => 1728068322011,
            ),
            2 =>
            array(
                'id' => '91noyULv8Mpa0jcUa0w9',
                'toId' => 'Lj8Yr6lPv2Xcj2YP9xrLzgzEV0B3',
                'toLastName' => 'Stuart',
                'toFirstName' => 'Sharms',
                'fromFirstName' => 'diksha jaswal',
                'message' => NULL,
                'fromId' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'fromLastName' => 'jaswaltyghjhui',
                'timestamp' => 1688534542396,
            ),
            3 =>
            array(
                'id' => '9IEwZjdiVEm7GKLb782m',
            ),
            4 =>
            array(
                'id' => '9bk76Xu1elwDQX0lKg98',
                'toId' => 'V3oIByoVrkYj4alDgq6Xf31Hztf1',
                'toLastName' => 'singh',
                'toFirstName' => 'rani',
                'fromFirstName' => 'sim',
                'message' => NULL,
                'fromId' => 'jiBAXwRmRyWz9fwGLMhKT7wF1nj1',
                'fromLastName' => 'singh',
                'timestamp' => 1690436066843,
            ),
            5 =>
            array(
                'id' => 'AIqAIbyonYjuYsxxnSiY',
                'toId' => 'Lj8Yr6lPv2Xcj2YP9xrLzgzEV0B3',
                'toLastName' => 'Stuart',
                'toFirstName' => 'Sharms',
                'fromFirstName' => 'harry',
                'message' => NULL,
                'fromId' => 'zRLB60U9TXe7AdI55PEOv22jLM03',
                'fromLastName' => 'singh',
                'timestamp' => 1689954789677,
            ),
            6 =>
            array(
                'id' => 'ApHXFCSqoB4fTOkLfnzU',
                'toId' => 'llZrzDFgaqOT7WQXgDcddzzOpsp1',
                'toLastName' => 'Stuart',
                'toFirstName' => 'Cassius',
                'fromFirstName' => 'diksha',
                'message' => 'Illegal Activity',
                'fromId' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'fromLastName' => 'jaswal',
                'timestamp' => 1684974647271,
            ),
            7 =>
            array(
                'id' => 'D5oZuKFTbOGfb1wbSdww',
                'toId' => 'eIVk4YhdgrZpDLWnKzvjJaNEKFP2',
                'toLastName' => '',
                'toFirstName' => '',
                'fromFirstName' => 'kavi',
                'message' => NULL,
                'fromId' => 'FSukDTJ3fBaoznR1Yfm5e6kBZeu2',
                'fromLastName' => 'sssss',
                'timestamp' => 1690370737830,
            ),
            8 =>
            array(
                'id' => 'EuhtZxZpFckb8ltkjmgF',
            ),
            9 =>
            array(
                'id' => 'FHm3D8Q9YiOrPDKkV9vo',
                'toId' => 'wllluukBFxWgEWHhtm8gd5RwZtP2',
                'toLastName' => 'Jane',
                'toFirstName' => 'Mary',
                'fromFirstName' => 'ousman',
                'message' => 'Underage User',
                'fromId' => 'v9hQHhLAclOIkSzLprOVaCO5Ziu1',
                'fromLastName' => 'sonko',
                'timestamp' => 1716847022791,
            ),
            10 =>
            array(
                'id' => 'GfhcxO57bAqjjyZzkWAg',
            ),
            11 =>
            array(
                'id' => 'HQvVeHBb5UFtLKdVfDJc',
            ),
            12 =>
            array(
                'id' => 'Hs2biwxeI43erfMsqWsh',
                'toId' => 'PSDNZO7Qw1OMnvSiYaTE8BUtb4b2',
                'toLastName' => 'Miller',
                'toFirstName' => 'Kerline',
                'fromFirstName' => 'Harry',
                'message' => NULL,
                'fromId' => 'Yu49EBChfNS1vbrEFUb40oUJDfD3',
                'fromLastName' => 'Singh',
                'timestamp' => 1687940250763,
            ),
            13 =>
            array(
                'id' => 'HyEZj7l133gnnF10iivr',
            ),
            14 =>
            array(
                'id' => 'I9BG44O6hlGA8S0M9xx9',
            ),
            15 =>
            array(
                'id' => 'Jbw4OBlA8g9HLi5TQaGj',
            ),
            16 =>
            array(
                'id' => 'K6GztxgEjjjesXHYgNG1',
            ),
            17 =>
            array(
                'id' => 'KfILu0AECSZrOVMCcCAg',
            ),
            18 =>
            array(
                'id' => 'Ms8Ln2apTkKXiK6WEWbf',
                'toId' => 'aDUzg3ThN3RSIDwjrkQ0hUEutyR2',
                'toLastName' => 'Roche',
                'toFirstName' => 'Maya',
                'fromFirstName' => 'Clyde',
                'message' => 'Child exploitation',
                'fromId' => 'xrbZTxfY3aXj4KVIys3wwayWTJx2',
                'fromLastName' => 'ufulk',
                'timestamp' => 1663707128417,
            ),
            19 =>
            array(
                'id' => 'O5F6Nb55Ti60Nib82JQE',
                'toId' => 'aDUzg3ThN3RSIDwjrkQ0hUEutyR2',
                'toLastName' => 'Roche',
                'toFirstName' => 'Maya',
                'fromFirstName' => 'sangeeta',
                'message' => 'Illegal Activity',
                'fromId' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'fromLastName' => 'Kumar',
                'timestamp' => 1682663689968,
            ),
            20 =>
            array(
                'id' => 'Q65vzgNNpwBVTQghY1Jc',
                'toId' => 'nSnnAx34OzOxgXGXsB4MnYYWR5R2',
                'toLastName' => 'doe 12',
                'toFirstName' => 'jane',
                'fromFirstName' => 'mehrosh',
                'message' => 'Illegal Activity',
                'fromId' => 'nalXkNDqMmcLVoGp8UWikwFc9by1',
                'fromLastName' => '',
                'timestamp' => 1665667005574,
            ),
            21 =>
            array(
                'id' => 'RdifaYyGty0YrpVeuWnJ',
                'toId' => 'ZENkFBbXhyPUFDIYmWH2IcUbrjo2',
                'toLastName' => 'Smith',
                'toFirstName' => 'Sally',
                'fromFirstName' => 'sangeeta',
                'message' => 'Span/Scam',
                'fromId' => 's5BnMAxileOBiyySbxnlRsx7uBt1',
                'fromLastName' => 'kumari',
                'timestamp' => 1692681848123,
            ),
            22 =>
            array(
                'id' => 'T1fT17Nj5KXahW3DORJA',
                'toId' => 'izkRWqI3XjTNmVoEGrOuXbFCFzx2',
                'toLastName' => 'miller',
                'toFirstName' => 'Anne',
                'fromFirstName' => 'test',
                'message' => NULL,
                'fromId' => 'nqgAlMKWXnV3T1lHqUbxKOKk1Hs2',
                'fromLastName' => 'test',
                'timestamp' => 1683016346498,
            ),
            23 =>
            array(
                'id' => 'VXUtNhG0LdMN7n45Cffk',
                'toId' => 'h56V4AAO0pSTi5JaMd26WpC9Nb23',
                'toLastName' => 'Sarah',
                'toFirstName' => 'DR',
                'fromFirstName' => 'folorunsho',
                'message' => 'Nudity or Sexual Content',
                'fromId' => '8YtUy8qCOddMVAjoBDX9W0f57JI2',
                'fromLastName' => 'oloruntoba',
                'timestamp' => 1728068318756,
            ),
            24 =>
            array(
                'id' => 'VcUmSjyipkFu5r6mTJcp',
                'toId' => 'evIrPrMcG8VYSExcyFNHjQfbeOI3',
                'toLastName' => '',
                'toFirstName' => '',
                'fromFirstName' => 'DR',
                'message' => 'Hate speech',
                'fromId' => 'h56V4AAO0pSTi5JaMd26WpC9Nb23',
                'fromLastName' => 'Sarah',
                'timestamp' => 1703093990334,
            ),
            25 =>
            array(
                'id' => 'WZEj7MvvAXq0wDe1MDPJ',
                'toId' => 'aDUzg3ThN3RSIDwjrkQ0hUEutyR2',
                'toLastName' => 'Roche',
                'toFirstName' => 'Maya',
                'fromFirstName' => '',
                'message' => 'Threats of violence',
                'fromId' => '5PZZFGCFskNJ3OkfXdpLuuxU09w1',
                'fromLastName' => '',
                'timestamp' => 1669308139271,
            ),
            26 =>
            array(
                'id' => 'XOTdCBTr6yROVFs6C4Gu',
                'toId' => '9aMkvQtabWYCJDzTtVWeVX2YRRY2',
                'toLastName' => 'micheala',
                'toFirstName' => 'f3',
                'fromFirstName' => 'sangeeta',
                'message' => NULL,
                'fromId' => 's5BnMAxileOBiyySbxnlRsx7uBt1',
                'fromLastName' => 'kumari',
                'timestamp' => 1692681607098,
            ),
            27 =>
            array(
                'id' => 'Xm80Qh9lac4YSJ5t7PAw',
                'toId' => 'Lj8Yr6lPv2Xcj2YP9xrLzgzEV0B3',
                'toLastName' => 'Stuart',
                'toFirstName' => 'Sharms',
                'fromFirstName' => 'diksha',
                'message' => NULL,
                'fromId' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'fromLastName' => 'jaswal',
                'timestamp' => 1688130781484,
            ),
            28 =>
            array(
                'id' => 'XtdkFANhD1fCMC10v3TH',
                'toId' => 'nSnnAx34OzOxgXGXsB4MnYYWR5R2',
                'toLastName' => 'doe 12',
                'toFirstName' => 'jane',
                'fromFirstName' => 'Gourav',
                'message' => NULL,
                'fromId' => 'ZcqiPbpuRPgz33EpFaTJ44vTnh42',
                'fromLastName' => 'bhabhoria',
                'timestamp' => 1674820985917,
            ),
            29 =>
            array(
                'id' => 'YUzQdRBJobBZuopnDsOq',
                'toId' => 'ZENkFBbXhyPUFDIYmWH2IcUbrjo2',
                'toLastName' => 'Smith',
                'toFirstName' => 'Sally',
                'fromFirstName' => 'sangeeta',
                'message' => 'Span/Scam',
                'fromId' => 's5BnMAxileOBiyySbxnlRsx7uBt1',
                'fromLastName' => 'kumari',
                'timestamp' => 1692681853718,
            ),
            30 =>
            array(
                'id' => 'ZknO6knKss9QgtGRNiYG',
                'toId' => 'Lj8Yr6lPv2Xcj2YP9xrLzgzEV0B3',
                'toLastName' => 'Stuart',
                'toFirstName' => 'Sharms',
                'fromFirstName' => 'diksha jaswal',
                'message' => NULL,
                'fromId' => '3Ljzvhipk7hUrI50lUeBDTwaSg53',
                'fromLastName' => 'jaswaltyghjhui',
                'timestamp' => 1689228859002,
            ),
            31 =>
            array(
                'id' => 'a6JfUF6SvAgLpxxWcyT4',
                'toId' => 'aDUzg3ThN3RSIDwjrkQ0hUEutyR2',
                'toLastName' => 'Roche',
                'toFirstName' => 'Maya',
                'fromFirstName' => 'sangeeta',
                'message' => 'Illegal Activity',
                'fromId' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'fromLastName' => 'Kumar',
                'timestamp' => 1682663695009,
            ),
            32 =>
            array(
                'id' => 'bJ61SN8CplHSm2rwLzcV',
                'toId' => 'aDUzg3ThN3RSIDwjrkQ0hUEutyR2',
                'toLastName' => 'Roche',
                'toFirstName' => 'Maya',
                'fromFirstName' => '',
                'message' => 'Illegal Activity',
                'fromId' => '5PZZFGCFskNJ3OkfXdpLuuxU09w1',
                'fromLastName' => '',
                'timestamp' => 1669386686955,
            ),
            33 =>
            array(
                'id' => 'bXLKv1P3IUTc76NYb6mA',
                'toId' => 'nSnnAx34OzOxgXGXsB4MnYYWR5R2',
                'toLastName' => 'doe 12',
                'toFirstName' => 'jane',
                'fromFirstName' => '',
                'message' => NULL,
                'fromId' => 'e4Fxa15PN1TGJdVVgI39lUEGumT2',
                'fromLastName' => '',
                'timestamp' => 1668757137352,
            ),
            34 =>
            array(
                'id' => 'eJxN0ER9abLb6vKn3jEO',
            ),
            35 =>
            array(
                'id' => 'ey8eg6Ubr70yXtJHYgnF',
                'toId' => '7qRETpoatmTI0Be5RHGI3j4ZdAS2',
                'toLastName' => 'fhchc',
                'toFirstName' => 'f12',
                'fromFirstName' => 'f10',
                'message' => NULL,
                'fromId' => 'jiBAXwRmRyWz9fwGLMhKT7wF1nj1',
                'fromLastName' => 'singh',
                'timestamp' => 1694666117606,
            ),
            36 =>
            array(
                'id' => 'gksXtAppz9NtorymeHEx',
                'toId' => 'tsOCkRHzSaeyhvCXv95KFvBJrhc2',
                'toLastName' => 'smith',
                'toFirstName' => 'Jennifer',
                'fromFirstName' => 'sangeeta',
                'message' => 'Illegal Activity',
                'fromId' => '3oiEMHsBlDPikYjRqH4R8ms4Jpk1',
                'fromLastName' => 'Kumar',
                'timestamp' => 1682673762433,
            ),
            37 =>
            array(
                'id' => 'iOecSHCNNHURn5tiXI8m',
            ),
            38 =>
            array(
                'id' => 'kS4gSFLvcUJJzzeHfYV1',
            ),
            39 =>
            array(
                'id' => 'mYIH0XWKA7wi4usrO3sH',
            ),
            40 =>
            array(
                'id' => 'ormTqBgffxIPq8NYLceZ',
            ),
            41 =>
            array(
                'id' => 'qb0mBU8HjXhkR0zTCHVK',
            ),
            42 =>
            array(
                'id' => 'rTu2ZJs8AdIdHs84bPHG',
                'toId' => 'evIrPrMcG8VYSExcyFNHjQfbeOI3',
                'toLastName' => '',
                'toFirstName' => '',
                'fromFirstName' => 'DR',
                'message' => 'Bullying',
                'fromId' => 'h56V4AAO0pSTi5JaMd26WpC9Nb23',
                'fromLastName' => 'Sarah',
                'timestamp' => 1703094244352,
            ),
            43 =>
            array(
                'id' => 'rhYc9J4iA6uV1IxYTbEi',
                'toId' => 'ousoFEpbtuYfLbB39DALpo5ILGy2',
                'toLastName' => 'Smith',
                'toFirstName' => 'Keth',
                'fromFirstName' => 'harry',
                'message' => NULL,
                'fromId' => '0nEtTI1nZZWrxBItbshWY0KkneS2',
                'fromLastName' => 'xvbn',
                'timestamp' => 1700640049880,
            ),
            44 =>
            array(
                'id' => 'sg0moI1RM9VN1pxLrgfz',
            ),
            45 =>
            array(
                'id' => 'sgtR7XhT8nO4muKQ8gQ4',
            ),
            46 =>
            array(
                'id' => 'syBRRtA8o9KfWqdFYFft',
            ),
            47 =>
            array(
                'id' => 'tnSaIZS1NVKxbO8VUnzW',
            ),
            48 =>
            array(
                'id' => 'uBTmaJb1l78BRkV7EoNw',
                'toId' => 'evIrPrMcG8VYSExcyFNHjQfbeOI3',
                'toLastName' => '',
                'toFirstName' => '',
                'fromFirstName' => '',
                'message' => NULL,
                'fromId' => '11QTtly5VLPGHGGWaZsbaRhursU2',
                'fromLastName' => '',
                'timestamp' => 1707881133994,
            ),
            49 =>
            array(
                'id' => 'uCORL3pRyibRjbxquXUm',
            ),
            50 =>
            array(
                'id' => 'uYWswt3NfvExPZflm0bN',
            ),
            51 =>
            array(
                'id' => 'vGWP8Az2bEW2LJoOftzk',
                'toId' => 'jiBAXwRmRyWz9fwGLMhKT7wF1nj1',
                'toLastName' => 'singh',
                'toFirstName' => 'f10',
                'fromFirstName' => 'sachin',
                'message' => 'Underage User',
                'fromId' => 'AkPTQfWkcAXzjETqMs2qhMITUCs2',
                'fromLastName' => 'chaufhary',
                'timestamp' => 1703497120075,
            ),
            52 =>
            array(
                'id' => 'vr2TjwanDWDyVahMhaSl',
            ),
            53 =>
            array(
                'id' => 'vz7x5DXfUSpHRHbZ8R1g',
            ),
        );
        DB::table('flagged_users')->truncate();
        foreach ($data as $item) {
            if (array_key_exists('toId', $item) && array_key_exists('fromId', $item)) {
                $from_user = User::where('uid', $item['fromId'])->first();
                $to_user = User::where('uid', $item['toId'])->first();
                if ($from_user && $to_user) {
                    FlaggedUser::create([
                        'uid' => $item['id'],
                        'from_user_id' => $from_user->id,
                        'to_user_id' => $to_user->id,
                        'from_first_name' => $item['fromFirstName'],
                        'from_last_name' => $item['fromLastName'],
                        'to_first_Name' => $item['toFirstName'],
                        'to_last_name' => $item['toLastName'],
                        'message' => $item['message'] ? $item['message'] : '---',
                    ]);
                }
            }
        }
    }
}
