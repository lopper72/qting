<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\MovieCategory;
use App\Models\MovieDetail;
use Illuminate\Console\Command;
use QL\QueryList;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Spider {type} {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider action';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $category_arr = [
        '纪录片' => '',
        '爱情片' => '',
        '动漫电影' => '',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $region_arr = $this->getRegion();
        $type = $this->argument('type') ?: 1;
        $page = (int)$this->argument('page') ?: 1;
        $base_url = "https://okzyw.com/";
        $list_url = "https://okzyw.com/?m=vod-type-id-1-pg-{$page}.html";
        $rules = [
            'date' => ['.xing_vb6','text'],
            'cate' => ['.xing_vb5','text'],
            'url' => ['a','href'],
        ];
        $range = '.xing_vb ul';
        $this->info("开始");
        do {
            $rt = QueryList::getInstance()->get($list_url)->rules($rules)->range($range)->query()->getData();
            $list = $rt->all();
            foreach ($list as $key => $value) {
                $this->info("第{$page}页 当前" . $key . '开始');
                $url = $value['url'];
                try {
                    if (empty($url)) {
                        throw new \Exception("url不正确");
                    }
                    preg_match("/\d+/is",$url,$id);
                    $relate_id = current($id);
                    if (empty($relate_id)) {
                        throw new \Exception("{$relate_id}不正确");
                    }
                    $ql = QueryList::getInstance()->get($base_url . $url);

                    $title = $ql->find('.vodh>h2')->text();
                    $thumb = $ql->find('.vodImg>img')->attr('src');
                    $intro = $ql->find('.vodplayinfo:eq(0)')->text();
                    $category = $ql->find('.vodinfobox>ul>li:eq(3)>span')->text();
                    $year = $ql->find('.vodinfobox>ul>li:eq(6)>span')->text();
                    $duration = $ql->find('.vodinfobox>ul>li:eq(7)>span')->text();
                    $score = $ql->find('.vodinfobox>ul>li:eq(11)>span')->text();
                    $release_date = $ql->find('.vodinfobox>ul>li:eq(6)>span')->text();
                    $release_address = $ql->find('.vodinfobox>ul>li:eq(4)>span')->text();
                    $view_num = $ql->find('.vodinfobox>ul>li:eq(9)>span')->text();
                    $detail_list = $ql->find('.vodplayinfo:eq(1)>div>div:eq(1)>ul>li')->texts();

                    $type_str = 'MOVIE';
                    if ($type == 2) {
                        $type_str = 'TV';
                    } elseif ($type == 3) {
                        $type_str = 'VARIETY';
                    } elseif ($type == 4) {
                        $type_str = 'COMIC';
                    }
                    $data = [];
                    $data['user_id'] = 2;
                    $data['category_id'] = MovieCategory::where('name', mb_substr($category, 0, 2))->value('id');
                    $data['type'] = $type_str;
                    $data['region'] = $region_arr[$release_address] ?? 'CHN';
                    $data['year'] = $year;
                    $data['title'] = $title;
                    $data['subtitle'] = $title;
                    $data['thumb'] = $thumb;
                    $data['intro'] = $intro;
                    $data['duration'] = $duration;
                    $data['score'] = ($score > 10) ? 9 : $score;
                    $data['release_date'] = $release_date;
                    $data['release_address'] = $release_address;
                    $data['tags'] = '';
                    $data['view_num'] = $view_num;
                    $data['relate_id'] = $relate_id;
                    $data['status'] = 2;
                    $data['updated_at'] = time();
                    $data['created_at'] = time();

                    $info = Movie::where('title', $title)->first();
                    if ($info) {
                        Movie::where('id', $info->id)->delete();
                        MovieDetail::where('movie_id', $info->id)->delete();
                    }

                    $movie_id = Movie::insertGetId($data);

                    foreach ($detail_list as $key => $detail) {
                        $arr = explode("$", $detail);
                        $detail_data = [];
                        $detail_data['user_id'] = 2;
                        $detail_data['relate_id'] = $relate_id;
                        $detail_data['movie_id'] = $movie_id;
                        $detail_data['sort'] = $key + 1;
                        $detail_data['title'] = $arr[0];
                        $detail_data['url'] = $arr[1];
                        $detail_data['status'] = 2;
                        $detail_data['updated_at'] = time();
                        $detail_data['created_at'] = time();
                        MovieDetail::insert($detail_data);
                    }
                } catch (\Exception $e) {
                    $this->info("第{$page}页 当前异常跳过{$e->getMessage()}");
                }
                $this->info("第{$page}页 当前" . $key . '结束');
            }
            $page--;
        } while($page >= 1);
        $this->info('全部结束');
    }

    /**
     * 获取地区
     */
    public function getRegion()
    {
        $result = [];
        foreach (Movie::$REGION as $value) {
            $result[$value['value']] = $value['key'];
        }
        return $result;
    }
}
