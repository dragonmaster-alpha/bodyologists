<?php

use App\Xml\SitemapXML as XMLGenerator;

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));

if ($administrator->admin_access($plugin_name)) {
    global $administrator, $frm, $helper, $meta, $settings;
    
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'Search Engine Optimization Settings';
            $items_info = $settings->get('SEO');
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.seo.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;

        case "update":

            try {
                if ($_POST['company_type'] == 'company') {
                    unset($_POST['person_name']);
                }

                if (!empty($_POST['snippet_codes'])) {
                    $_POST['snippet_codes'] = $helper->filter($_POST['snippet_codes'], 0, 1);
                }
                
                foreach ($_POST as $key => $value) {
                    if ($key != 'op' && $key != 'robotText' && $key != 'plugin' && $key != 'file') {
                        $_save_array[$key] = $value;
                    }
                }

                # Schema contruction
                if (!empty($_save_array['company_type'])) {
                    if ($_save_array['company_type'] == 'company') {
                        $_company_schema['@context'] = 'http://schema.org';
                        $_company_schema['@type'] = 'Organization';
                        $_company_schema['name'] = $_save_array['sitename'];
                        $_company_schema['url'] = 'http'.(!empty($helper->config['force_https']) ? 's' : '').'://'.$_save_array['url_syntax'].$helper->site_domain()._SITE_PATH;
                        $_company_schema['logo'] = 'http'.(!empty($helper->config['force_https']) ? 's' : '').'://'.$_save_array['url_syntax'].$helper->site_domain()._SITE_PATH.$_save_array['company_logo'];
                        
                        if (!empty($_save_array['facebook_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['facebook_address'];
                        }
                        if (!empty($_save_array['twitter_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['twitter_address'];
                        }
                        if (!empty($_save_array['google_plus_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['google_plus_address'];
                        }
                        if (!empty($_save_array['pinterest_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['pinterest_address'];
                        }
                        if (!empty($_save_array['linkedin_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['linkedin_address'];
                        }
                        if (!empty($_save_array['youtube_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['youtube_address'];
                        }
                        if (!empty($_save_array['blogger_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['blogger_address'];
                        }
                        if (!empty($_save_array['flickr_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['flickr_address'];
                        }
                        if (!empty($_save_array['wordpress_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['wordpress_address'];
                        }
                        if (!empty($_save_array['delicious_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['delicious_address'];
                        }
                        if (!empty($_save_array['instagram_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['instagram_address'];
                        }
                        if (!empty($_save_array['stumbleupon_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['stumbleupon_address'];
                        }
                        if (!empty($_save_array['vimeo_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['vimeo_address'];
                        }
                        if (!empty($helper->config['address'])) {
                            $_company_schema['address'] = [
                                '@type' => 'PostalAddress',
                                'streetAddress' => $helper->config['address'],
                                'addressLocality' => $helper->config['city'],
                                'addressRegion' => $helper->config['state'],
                                'postalCode' => $helper->config['zipcode'],
                                'addressCountry' => $helper->config['country']
                            ];
                        }
                        if (!empty($helper->config['phone'])) {
                            $_company_schema['telephone'] = '+'.$helper->config['phone'];
                        }
                    } elseif ($_save_array['company_type'] == 'personal') {
                        $_company_schema['@context'] = 'http://schema.org';
                        $_company_schema['@type'] = 'Person';
                        $_company_schema['url'] = 'http'.(!empty($helper->config['force_https']) ? 's' : '').'://'.$_save_array['url_syntax'].$helper->site_domain()._SITE_PATH;
                        $_company_schema['name'] = $_save_array['person_name'];
                        if (!empty($_save_array['facebook_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['facebook_address'];
                        }
                        if (!empty($_save_array['twitter_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['twitter_address'];
                        }
                        if (!empty($_save_array['google_plus_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['google_plus_address'];
                        }
                        if (!empty($_save_array['pinterest_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['pinterest_address'];
                        }
                        if (!empty($_save_array['linkedin_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['linkedin_address'];
                        }
                        if (!empty($_save_array['youtube_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['youtube_address'];
                        }
                        if (!empty($_save_array['blogger_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['blogger_address'];
                        }
                        if (!empty($_save_array['flickr_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['flickr_address'];
                        }
                        if (!empty($_save_array['wordpress_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['wordpress_address'];
                        }
                        if (!empty($_save_array['delicious_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['delicious_address'];
                        }
                        if (!empty($_save_array['instagram_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['instagram_address'];
                        }
                        if (!empty($_save_array['stumbleupon_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['stumbleupon_address'];
                        }
                        if (!empty($_save_array['vimeo_address'])) {
                            $_company_schema['sameAs'][] = $_save_array['vimeo_address'];
                        }
                    }
                }

                if (!empty($_save_array['sitename'])) {
                    $_website_schema['@context'] = 'http://schema.org';
                    $_website_schema['@type'] = 'WebSite';
                    $_website_schema['name'] = $_save_array['sitename'];
                    $_website_schema['url'] = 'http'.(!empty($helper->config['force_https']) ? 's' : '').'://'.$_save_array['url_syntax'].$helper->site_domain()._SITE_PATH;
                    if (!empty($_save_array['alternative_name'])) {
                        $_website_schema['alternateName'] = $_save_array['alternative_name'];
                    }
                }

                $_manifesto['name'] = $_save_array['sitename'];
                if (!empty($_save_array['alternative_name'])) {
                    $_manifesto['short_name'] = $_save_array['alternative_name'];
                }
                $_manifesto['start_url'] = 'http'.(!empty($helper->config['force_https']) ? 's' : '').'://'.$_save_array['url_syntax'].$helper->site_domain()._SITE_PATH;
                $_manifesto['display'] = 'standalone';
                $_manifesto['orientation'] = 'portrait-primary';
                $_icons = ['57x57', '76x76', '120x120', '152x152'];

                foreach ($_icons as $_site_icons) {
                    $_manifesto['icons'][] = [
                        'src' => 'images/ico/apple-touch-icon-'.$_site_icons.'-precomposed.png',
                        'sizes' => $_site_icons,
                        'type' => 'image/png'
                    ];
                }

                $_manifesto['related_applications'][] = [
                    'platform' => 'web'
                ];

                $settings->set('SEO', $_save_array);
                file_put_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/company.schema.json', json_encode($_company_schema));
                file_put_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/website.schema.json', json_encode($_website_schema));
                file_put_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/manifesto.json', json_encode($_manifesto));

                $_SESSION['message'] = 'SEO settings modification request successfully executed.';
                $administrator->record_log("SEO Settings modified", "A new SEO settings modification request has been executed");
                $helper->redirect('admin/admin.php?plugin=system&file=seo');
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $helper->redirect('admin/admin.php?plugin=system&file=seo');
            }
            
        break;
        
        case "update_xml":

            try {
                $xml_generator = new XMLGenerator;

                $_SESSION['message'] = 'The request to update the Sitemap XML file has being successfully executed.';
                $administrator->record_log("Sitemap XML updated", "The request to update the Sitemap XML file has being successfully executed.");
                $helper->redirect('admin/admin.php?plugin=system&file=seo');
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $helper->redirect('admin/admin.php?plugin=system&file=seo');
            }

        break;

        case 'seo':

            $items_info = $settings->get('SEO');
            
            ob_start();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.seo.info.phtml');
            ob_end_flush();

        break;

        case 'check':

            $data = $helper->filter($_POST);
            $keyword = $data['extras']['key'];

            require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/app/formatters/class.dom.php');
            //require_once($_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/kernel/vendor/autoload.php');
            //$textStatistics                                         = new DaveChild\TextStatistics\TextStatistics;

            $_site_domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
            $_points = [];

            # Title Checkout
            if (strlen($data['meta_title']) < 35) {
                $return['title']['text'] = _TITLE_TOO_SHORT;
                $return['title']['color'] = 'msgerror';
                $_points[] = 33;
            } elseif (strlen($data['meta_title']) > 70) {
                $return['title']['text'] = _TITLE_TOO_LARGE;
                $return['title']['color'] = 'msgalert';
                $_points[] = 66;
            } elseif (stripos($data['meta_title'], $keyword) === false) {
                $return['title']['text'] = _TITLE_NO_KEY_IN_TITLE;
                $return['title']['color'] = 'msgerror';
                $_points[] = 33;
            } else {
                $return['title']['text'] = _TITLE_GOOD;
                $return['title']['color'] = 'msgsuccess';
                $_points[] = 100;
            }

            # Meta description checkout
            if (strlen($data['meta_description']) < 35) {
                $return['description']['text'] = _DESCRIPTION_TOO_SHORT;
                $return['description']['color'] = 'msgerror';
                $_points[] = 33;
            } elseif (strlen($data['meta_description']) > 160) {
                $return['description']['text'] = _DESCRIPTION_TOO_LARGE;
                $return['description']['color'] = 'msgalert';
                $_points[] = 66;
            } elseif (stripos($data['meta_description'], $keyword) === false) {
                $return['description']['text'] = _DESCRIPTION_NO_KEY_IN_DESCRIPTION;
                $return['description']['color'] = 'msgerror';
                $_points[] = 33;
            } else {
                $return['description']['text'] = _DESCRIPTION_GOOD;
                $return['description']['color'] = 'msgsuccess';
                $_points[] = 100;
            }

            # URL checkout
            if (strlen($data['url']) < 7) {
                $return['url']['text'] = _URL_TOO_SHORT;
                $return['url']['color'] = 'msgerror';
                $_points[] = 33;
            } elseif (strlen($data['url']) > 50) {
                $return['url']['text'] = _URL_TOO_LARGE;
                $return['url']['color'] = 'msgalert';
                $_points[] = 66;
            } elseif (stripos(str_replace('-', ' ', $data['url']), $keyword) === false) {
                $return['url']['text'] = _URL_NO_KEY_IN_URL;
                $return['url']['color'] = 'msgerror';
                $_points[] = 33;
            } else {
                $return['url']['text'] = _URL_GOOD;
                $return['url']['color'] = 'msgsuccess';
                $_points[] = 100;
            }

            # Text checkout
            //$words                                                  = $textStatistics::wordCount($data['text']);

            if (str_word_count($data['text']) < 300) {
                $return['text']['text'] = _TEXT_TOO_SHORT;
                $return['text']['color'] = 'msgerror';
                $_points[] = 33;
            } else {
                $return['text']['text'] = _TEXT_GOOD;
                $return['text']['color'] = 'msgsuccess';
                $_points[] = 100;
            }

            // $fleschKincaid                                          = $textStatistics->fleschKincaidReadingEase($data['text']);

            // if($fleschKincaid >= 80)
            // {
            //     $return['flesch']['text']                           = _READING_EASE_GREAT;
            //     $return['flesch']['color']                          = 'msgsuccess';
            //     $_points[]                                          = 100;
            // }
            // else if($fleschKincaid < 80 && $fleschKincaid >= 60)
            // {
            //     $return['flesch']['text']                           = _READING_EASE_GOOD;
            //     $return['flesch']['color']                          = 'msgsuccess';
            //     $_points[]                                          = 80;
            // }
            // else if($fleschKincaid < 60 && $fleschKincaid >= 40)
            // {
            //     $return['flesch']['text']                           = _READING_EASE_NO_GOOD;
            //     $return['flesch']['color']                          = 'msgalert';
            //     $_points[]                                          = 66;
            // }
            // else if($fleschKincaid < 40)
            // {
            //     $return['flesch']['text']                           = _READING_EASE_BAD;
            //     $return['flesch']['color']                          = 'msgerror';
            //     $_points[]                                          = 33;
            // }

            if (!empty($data['text'])) {
                $html = @str_get_html($data['text']);

                foreach ($html->find('p') as $k => $p) {
                    if (empty($k)) {
                        if (stripos($p, $keyword) === false) {
                            $return['p']['text'] = _FIRST_PARAGRAPH_NO_KEYWORD;
                            $return['p']['color'] = 'msgerror';
                            $_points[] = 33;
                        } else {
                            $return['p']['text'] = _FIRST_PARAGRAPH_GOOD;
                            $return['p']['color'] = 'msgsuccess';
                            $_points[] = 100;
                        }
                    }
                }

                foreach ($html->find('img') as $k => $img) {
                    if (empty($img->alt)) {
                        $_points[] = 33;
                        $_img_no_alt++;
                    }
                    $img_cnt++;
                }

                foreach ($html->find('a') as $k => $a) {
                    if (empty($a->href)) {
                        $return['a'][$k]['text'] = _A_NEED_LINK;
                        $return['a'][$k]['color'] = 'msgerror';
                        $_points[] = 33;
                    } elseif (empty($a->title)) {
                        $return['a'][$k]['text'] = _A_NEED_TITLE_TEXT;
                        $return['a'][$k]['color'] = 'msgerror';
                        $_points[] = 33;
                    } else {
                        $return['a'][$k]['color'] = 'msgsuccess';
                        $_points[] = 100;
                    }

                    if (!stripos($a->href, $_site_domain)) {
                        if (!empty($a->rel) && $a->rel != 'nofollow') {
                            $return['a'][$k]['text'] = _A_EXTERNAL_NEED_NOFOLLOW;
                            $return['a'][$k]['color'] = 'msgerror';
                            $_points[] = 33;
                        }
                        $nofollow_cnt++;
                        $a_out++;
                    } else {
                        $a_local++;
                    }

                    $a_cnt++;
                }
            }

            $_totals = round(array_sum($_points) / count($_points));

            if ($_totals > 60) {
                $_total_color = 'bluebar';
            } elseif ($_totals > 40) {
                $_total_color = 'orangebar';
            } else {
                $_total_color = 'redbar';
            }

            ?>
                <div class="progress" style="width: 86%;">
                    <div class="bar2"><div class="value <?=$_total_color?>" style="width: <?=$_totals?>%;">Overall SEO (<?=$_totals?>%)</div></div>
                </div>
                <div class="notibar <?=$return['title']['color']?>" style="width: 86%;">
                    <p><?=$return['title']['text']?></p>
                </div>
                <div class="notibar <?=$return['description']['color']?>" style="width: 86%;">
                    <p><?=$return['description']['text']?></p>
                </div>
                <div class="notibar <?=$return['url']['color']?>" style="width: 86%;">
                    <p><?=$return['url']['text']?></p>
                </div>
                <div class="notibar <?=$return['p']['color']?>" style="width: 86%;">
                    <p><?=$return['p']['text']?></p>
                </div>
                <?if (!empty($a_out)) {?>
                <div class="notibar <?=(!empty($nofollow_cnt) ? 'msgalert' : 'msgsuccess')?>" style="width: 86%;">
                    <p>The page content has <?=$a_out?> outside link<?=($a_out != 1 ? 's' : '')?> with <?=$nofollow_cnt?> link with not nofollow attributes.</p>
                </div>
                <?}?>
                <?if (!empty($_img_no_alt)) {?>
                <div class="notibar msgerror" style="width: 86%;">
                    <p>The page content has <?=$_img_no_alt?> image<?=($_img_no_alt != 1 ? 's' : '')?> without alt text. It is highly recommendable to set alt text on every image.</p>
                </div>
                <?}?>
            <?php
            die;
            
        break;
    }
} else {
    header("Location: index.php");
    exit();
}
