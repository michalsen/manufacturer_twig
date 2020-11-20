<?php
namespace Drupal\manufacturer_twig\TwigExtension;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;

class Manufacturers extends \Twig_Extension {

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'cncextwig.twig_extension';
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('mfr_list', array($this, 'mfrList'), array('is_safe' => array('html')))
    ];
  }

  /**
   * Replaces all numbers from the string.
   */
  public static function mfrList($string) {

    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    $list = [];

    $taxonomy = taxonomy_term_load($tid);
    $taxName = $taxonomy->getName();


    if ($taxonomy->get('vid')->target_id == 'manufacturer') {
      $field = 'field_manufacturer_taxonomy';
    }
    if ($taxonomy->get('vid')->target_id == 'machine_type') {
      $field = 'field_machine_type';
    }

    $result = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([$field => $tid]);

    $item = '<div class="view-content">';


    foreach ($result as $nid => $value) {
      $list[] = $nid;
    }

    if(count($list) < 1) {
      $item .= 'We currently do not have any ' . $taxName . ' machines in stock but check back soon as our inventory is constantly changing, and the website is updated on a daily basis.';
    }


    foreach ($list as $nid) {
      // Load Machine Node
            $node_storage = \Drupal::entityTypeManager()->getStorage('node');
            $node = $node_storage->load($nid);

            // Machine Image
            $image = $file = $node->field_product_image->entity;
            $url = '';

            // Machine URL
            if ($file != NULL) {
              $uri = $file->getFileUri();
              $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('grid_480x300');
              $url = $style->buildUrl($uri);
            }

            // Manufacturer Taxonomy Name
            $manufacturer_term = Term::load($node->get("field_manufacturer_taxonomy")->target_id);

            if ($manufacturer_term->getName() == 'Other') {
              $manufacturer_term_name = $node->get("field_manufacturer")->value;
            }
             else {
              $manufacturer_term_name = $manufacturer_term->getName();
             }

      $alias_path = $node->toUrl()->toString();

      $item .= '
        <div class="views-row">
        <div class="views-field views-field-field-featured-image">
          <div class="field-content">
            <img src="' . $url . '" title="' . $node->get("title")->value . '" typeof="foaf:Image">
          </div>
        </div>
      <div class="views-field views-field-path">
        <span class="field-content">
          <div class="sec top">
    	      <div class="desc">
              <p>' . $node->get("field_machine_title")->value . '</p>
            </div>
    	    </div>
          <div class="sec bottom">
    	      <div class="mfr">
    	        ' . $manufacturer_term_name . '
              <br>
    	        ' . $node->get("field_machine_model")->value . '
    	      </div>
          	<div class="title">
    	        YEAR: ' . $node->get("field_manufacturing_year")->value . '
              <br>
    	        ' . $node->get("field_stock_number")->value . '
    	      </div>
    	      <a href="' . $alias_path . '" class="btn sm red">View Details</a>
         </div>
            <a href="' . $alias_path . '" class="overlay-link">
              <div class="hover-text">' . $node->get("field_ad_line")->value . '
              </div>
                <span class="btn sm red">View Details</span>
            </a>
          </span>
      </div>
    </div>';

    }

    $item .= '</div>';
    return $item;

  }

}
