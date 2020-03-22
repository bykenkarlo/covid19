<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {
    public function getAPIData(){
        $countryInput = $this->input->get('country');
        $url = 'https://corona.lmao.ninja/countries/'.$countryInput;
        $data = json_decode(file_get_contents($url, false));
        
        $dataInfo = array(
            'country'=>$data->country,
            'cases'=>$data->cases,
            'todayCases'=>$data->todayCases,
            'deaths'=>$data->deaths,
            'todayDeaths'=>$data->todayDeaths,
            'recovered'=>$data->recovered,
            'critical'=>$data->critical,
            'casesPerOneMillion'=>$data->casesPerOneMillion,
            'activeCases'=> $data->cases - $data->recovered - $data->deaths,
            'closeCases'=> $data->recovered + $data->deaths,
        );
        $closeCases = $data->recovered + $data->deaths;
        $activeCases = $data->cases - $data->recovered - $data->deaths;
        $dataInfo['mildCases'] = $activeCases - $data->critical;

        $dataInfo['mildCasesCasesPercent'] = round( $dataInfo['mildCases'] / $activeCases * 100, 2);
        $dataInfo['criticalCasesCasesPercent'] = round( $data->critical / $activeCases * 100, 2);

        $dataInfo['recoverCasesPercent'] = round( $data->recovered / $closeCases * 100, 2);
        $dataInfo['deathsCasesPercent'] = round( $data->deaths / $closeCases * 100, 2);

        // return $dataInfo;
        $this->output->cache('15');
        $this->output->set_content_type('application/json')->set_output(json_encode($dataInfo));

    }
    public function getHistoricalData(){
        $countryInput = $this->input->get('country');
        $url = 'https://corona.lmao.ninja/historical/'.$countryInput;
        $data = json_decode(file_get_contents($url, false));
        if($data){
            $data = array(
                'timeline'=>$data->timeline,
            );
            $this->output->cache('15');
            $data['status'] = 'Connected';
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
    public function getRssFeed(){
        $news = simplexml_load_file('https://news.google.com/rss?hl=en-PH&gl=PH&ceid=PH:en&search?q=coronavirus,covid19');

        $feeds = array();
        $i = 0;

        foreach ($news->channel->item as $item) {
            preg_match('@src="([^"]+)"@', $item->description, $match);
            $parts = explode('<font size="-1">', $item->description);
            
            $feeds[$i]['title'] = (string) $item->title;
            $feeds[$i]['source'] = (string) $item->source;
            $feeds[$i]['url'] = (string) $item->link;
            $i++;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($feeds));
        // echo json_encode($feeds);
    }
    public function webScrap(){
       
        // require 'simple_html_dom.php';
        require_once(APPPATH.'libraries/simple_html_dom.php');

        $html = file_get_html('https://ncovtracker.doh.gov.ph');
        $title = $html->find('div.external-html', 0);
        // $image = $html->find('img', 0);
        // $link = $html->find('a', 0);

        echo $title->plaintext."<br>\n";
        // echo $image->src;
        // echo $link->href;
    }
}
