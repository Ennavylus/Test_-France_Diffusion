<?php
require __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->in(__DIR__)->in("./")->name('france-diffusion.csv');

$data = [];
foreach ($finder as $file) {
    $absoluteFilePath = $file->getRealPath();
    $fileR = fopen($absoluteFilePath, 'r');
    while (($line = fgetcsv($fileR)) !== FALSE)
    {
        $temp = explode(";",$line[0]);
        $populationValue = isset($temp[1]) ? intval(str_replace(' ', '', $temp[1])) :  null; 
        if(!$populationValue)
        {
            $data['header'] = ["city" => $temp[0], "value" => $temp[1]]; 
        }
        elseif($populationValue && $populationValue > 30000)
        {
            $data['data'][] = ['city' => $temp[0], 'value'=> $temp[1], 'isRed' => (  $populationValue > 50000 ?: 0)];  
        }
    }
    fclose($fileR);
    break;
}

if(isset($data["data"]))
{
    usort($data['data'], 'sortByAlphabetical');
}

print_r('<h1>France Diffusion</h1>'); 
print_r(createHtmlTable($data));



function sortByAlphabetical($a, $b) {
    return $a['city'] > $b['city'];
}

function createHtmlTable($data)
{
    if(empty($data))
    {
        return "Aucune données utilisable.";
    }
    $html = "<table>";
    if(isset($data['header']))
    {
        $html .= "<thead><tr>"; 
        $html .= "<td>" . $data['header']['city'] . "</td>";
        $html .= "<td>" . $data['header']['value'] . "</td>";
        $html .= "</tr></thead>";
    }

    if(isset($data['data']))
    {
        $html .= "<tbody>"; 
        foreach($data['data'] as $key => $data)
        {
            $html .= "<tr" . ($key%2 ? ' class="bg-grey"': '') . ">";
            $html.= "<td" . ($data['isRed'] ? ' class="name-red"': '') . ">" . $data['city'] . "</td>";
            $html .= "<td>" . $data['value'] . "</td></tr>";
        }
        $html .= "</tbody>";
    }
    $html .= '</table>';
    $html .= '<style>
                table 
                {
                    border-collapse: collapse;
                }
                td
                {
                    padding: 6px; 
                    border: solid 1px #000;
                }
                thead >tr >td 
                {
                    
                    font-weight: bolder; 
                }
                .bg-grey
                {
                    background-color:#e1e1e1; 
                }
                .name-red
                {
                    color:FF0000;
                    font-weight: bolder; 
                }
                </style>';
    return $html; 
}


