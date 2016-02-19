<?php
/**
 * @package     ##package##
 * @subpackage  ##subpackage##
 * @author      ##author##
 * @copyright   ##copyright##
 * @license     ##license##
 * @version     ##version##
 */
 
// no direct access
defined('_JEXEC') or die;

define('ZEN_ALLOW', 1);
define('JOOMLA', 1);
define('WP', 0);

jimport('joomla.filesystem.folder');
jimport( 'joomla.filesystem.file' );


class plgAjaxZengridframework extends JPlugin
{

	function onAjaxZengridframework()
       {
       		$app = JFactory::getApplication();
		
			// Only use the plugin in the admin
			if (!$app->isAdmin()) return;
			  
		 	$jinput = JFactory::getApplication()->input;
		 	$content 	= $jinput->get('content', '', 'RAW');
		 	$action = $jinput->get('action', '', 'RAW');
		 	$template = $jinput->get('template');
		 	$target = $jinput->get('target', '', 'RAW');
		 	$name = strtolower(str_replace(' ','-', $jinput->get('name', '', 'RAW')));
			$id = $jinput->get('id', '', 'RAW');
			
			define( 'ROOT_PATH', JPATH_ROOT);
			define( 'TEMPLATE_PATH', JPATH_ROOT.'/templates/'.$template.'/');
			define( 'TEMPLATE_PATH_RELATIVE', 'templates/'.$template.'/');
			define( 'TEMPLATE_URI', JURI::base() . '/templates/'.$template);
			define('FRAMEWORK_PATH', TEMPLATE_PATH.'/zengrid');

		 
		 	return self::$action($content, $id, $target, $template, $name);	
		}
       
       
	 
	 	/**
 		* 	Function to save layouts, configs, themes
 		*	Receives content in array
 		* 	
 		*/
	 		
       private function save($data, $id, $target, $template, $name) {
  
			$content = json_encode($data, JSON_PRETTY_PRINT);
       		$path = JPATH_ROOT .'/templates/'.$template.'/settings/'.$target.'/';
       		
       		if($target =="config") {
       			$name = 'config-'.$id;
       		}
       		$fileName = $path . $name . '.json';
 
       		JFile::write($fileName, $content);
       }
       
       
       /**
   		* 	Function to delete layouts, configs, themes
   		*	
   		* 	
   		*/
   	 		
         private function delete_theme($data, $id, $target, $template, $name) {
    
   			
   				if(!empty($data)){
   					$theme = $data;
   					
   					if($theme !=="") {
   						$base = TEMPLATE_PATH.'css/theme.'.$theme;
   						echo $base;
   						JFile::delete($base.'.css');
   						JFile::delete($base.'.php');
   						JFile::delete($base.'.map');
   						JFile::delete(TEMPLATE_PATH.'settings/themes/theme.'.$theme.'.json');
   						
   						return 'The '.$theme.' and assets have been deleted';
   						
   					} else {
   						return 'No theme to delete';
   					}
   				}
   				else {
   					return 'No theme to delete';
   				}
   			}
       
             
        
        
        /**
		* 	Load layout
		*	
		* 	
		*/
        	 		
          private function set_layout($data, $id, $target, $template, $name) {
     
    		 // Include Helpers
    		 	include FRAMEWORK_PATH . '/helpers/helper.php'; 
    		 	
    		 	// Instantiate $zgf
    		 	$zgf = new zen();
    		 	
    		 	// Template Path
    		 	$template_path = TEMPLATE_PATH;
    		 	$layoutpath		= TEMPLATE_PATH.'/settings/layouts/';
    		 	
    		 	// Files from Assets.xml
    		 	$layout = $_POST['layout'];
    		 	
    		 	// Get Theme blocks
    		 	// We may be dending a name of a layout to retrieve or sending the data from a config file.
    		 	
    		 	if(is_array($layout)) {
    		 		$theme_layout = json_decode(json_encode($layout));
    		 	} else {
    		 		$theme_layout = $zgf->get_json('settings/layouts/'.$layout.'.json');
    		 	}
    		 	
    		 	
    		 	// Compare keys with default layout with used layouts
    		 	if(file_exists(TEMPLATE_PATH . 'custom/positions.json')) {
    		 		$default_layout = $zgf->get_json('custom/positions.json');
    		 	} else {
    		 		$default_layout = $zgf->get_json('settings/layouts/positions.json');
    		 	}
    		 	
    		 	foreach ($default_layout as $key => $row) { 
    		 		
    		 		if($key !=="main") {
    		 			echo '<div id="'.$key.'" data-row="'.$key.'" class="module-row">';
    		 			echo '<div class="row-title"><span>'.$key.'</span>'.
    		 			'<div class="stack-position">'.
    		 			'<div><span class="icon-desktop"></span>'.
    		 			'<a class="stack-positions" id="hidden-desktop" href="#">Hide</a></div>'. 
    		 			'<div><span class="icon-tablet"></span>'.
    		 			'<a class="stack-positions" id="stack-tablets" href="#">Stack</a>'.
    		 			'<a class="stack-positions" id="hidden-tablets" href="#">Hide</a>'.
    		 			'<a class="stack-positions" id="no-change-phones" href="#">No Change</a></div>'.
    		 			'<div><span class="icon-mobile"></span>'.
    		 			'<a href="#" class="stack-positions" id="stack-phones">Stack</a>'.
    		 			'<a href="#" class="stack-positions" id="hidden-phones">Hide</a>'.
    		 			'<a class="stack-positions" id="no-change-phones" href="#">No Change</a>'.
    		 			'</div></div></div>';
    		 			
    		 			foreach ($row as $title => $width) {
    		 				
    		 				if(isset($theme_layout->$key->{'positions'}->{$title})) {
    		 					$width = $theme_layout->$key->{'positions'}->{$title};
    		 				}
    		 				
    		 				echo '<div id="'.$title.'" class="resizable ui-widget-content grid-'.$width.'" data-width="'.$width.'" style="display:none" data-active="0">';
    		 				echo '<h3 class="ui-widget-header">';
    		 				echo '<span class="icon-eye"></span>';
    		 				echo '<span class="col-count"></span>'.ucfirst($title).' </h3>';
    		 				echo '</div>';
    		 				
    		 			}
    		 			
    		 			echo '</div>';
    		 			echo '<div data-id="'.$key.'" class="unused-modules has-content"><span class="icon-eye cancel"></span>';
    		 				
    		 				foreach ($row as $title => $width) {
    		 					echo '<div data-id="'.$title.'" style="display:block">'.ucfirst($title).'</div>';
    		 				}
    		 				
    		 			echo '</div>';	
    		 		}
    		 		
    		 		else {
    		 			
    		 			echo '<div id="'.$key.'" data-row="'.$key.'" class="module-row">';
    		 				echo '<div class="row-title"><span>'.$key.'</span><a class="main-content-toggle" href="#main-left-right" id="maincontent_sidebar1_sidebar2">Main Left Right</a> / <a class="main-content-toggle" href="#left-main-right" id="sidebar1_maincontent_sidebar2">Left Main Right</a> / <a class="main-content-toggle" href="#left-right-main" id="sidebar1_sidebar2_maincontent">Left Right Main</a>'.
    		 				'<div class="stack-position">'.
    		 				'<div><span class="icon-desktop"></span>'.
    		 				'<a class="stack-positions" id="hidden-desktop" href="#">Hide</a></div>'. 
    		 				'<div><span class="icon-tablet"></span>'.
    		 				'<a class="stack-positions" id="stack-tablets" href="#">Stack</a>'.
    		 				'<a class="stack-positions" id="hidden-tablets" href="#">Hide</a>'.
    		 				'<a class="stack-positions" id="no-change-tablets" href="#">No Change</a></div>'.
    		 				'<div><span class="icon-mobile"></span>'.
    		 				'<a href="#" class="stack-positions" id="stack-phones">Stack</a>'.
    		 				'<a href="#" class="stack-positions" id="hidden-phones">Hide</a>'.
    		 				'<a class="stack-positions" id="no-change-phones" href="#">No Change</a>'.
    		 				'</div></div></div>';
    		 				
    		 				// Default main positions
    		 				$default_main = (array)$default_layout->main;
    		 				
    		 				// Used positions
    		 				$mainlayout = 	(array)$theme_layout->main->positions;
    		 				
    		 				// Get any iotems not used but should be in array
    		 				// Items in this will be nulled and in the hidden row.
    		 				$diff = array_diff_key($default_main,$mainlayout);
    		 				
    		 				
    		 				foreach ($mainlayout as $title => $width) {
    		 					echo '<div id="'.$title.'" class="resizable ui-widget-content grid-'.$width.'" data-width="'.$width.'">';
    		 					echo '<h3 class="ui-widget-header">';
    		 					echo '<span class="icon-eye"></span>';
    		 					echo '<span class="col-count"></span>'.ucfirst($title).' </h3>';
    		 					echo '</div>';
    		 				}
    		 				
    		 				foreach ($diff as $title => $width) {
    		 					echo '<div id="'.$title.'" class="resizable ui-widget-content grid-'.$width.' hidden" data-width="'.$width.'">';
    		 					echo '<h3 class="ui-widget-header">';
    		 					echo '<span class="icon-eye"></span>';
    		 					echo '<span class="col-count"></span>'.ucfirst($title).' </h3>';
    		 					echo '</div>';
    		 				}
    		 				
    		 				echo '</div>';
    		 			
    		 				
    		 				echo '<div data-id="'.$key.'" class="unused-modules has-content"><span class="icon-eye cancel"></span>';
    		 					
    		 					foreach ($row as $title => $width) {
    		 						echo '<div data-id="'.$title.'">'.ucfirst($title).'</div>';
    		 					}
    		 					
    		 				echo '</div>';
    		 				
    		 		}
    		 	} 
    		 	
    		 	ob_start(); ?>
    		 	
    		 	
    		 	<script>
    		 		
    		 		jQuery(document).ready(function($) {
    		 
    		 			<?php // Loads the layout on first page laod
    		 				foreach ($theme_layout as $key => $row) { 
    		 		
    		 				// Get state for classes
    		 				$classes = $row->classes;
    		 				$classes = explode(' ', $classes->classes);
    		 				
    		 				foreach ($classes as $class) {
    		 					if($class!=="") { ?>
    		 						$("#resize-container #<?php echo $key;?> #<?php echo $class;?>").addClass("active");
    		 					<?php }
    		 				}
    		 				
    		 				// Get positions
    		 				$positions= $row->positions;
    		 				if(is_object($positions)) {
    		 				
    		 			
    		 				foreach ($positions as $module => $item) { ?>		
    		 					$("#resize-container #<?php echo $module;?>").attr("data-active", "1").show();
    		 					$("#resize-container .unused-modules [data-id='<?php echo $module;?>']").hide();
    		 				<?php } ?>
    		 				
    		 				var unused_modules = $('[data-id="<?php echo $key;?>"].unused-modules div:visible').length;
    		 				
    		 				if(unused_modules > 0) {
    		 					$('[data-id="<?php echo $key;?>"].unused-modules').addClass('has-content');
    		 				} else {
    		 					$('[data-id="<?php echo $key;?>"].unused-modules').removeClass('has-content');
    		 				}
    		 				
    		 				
    		 				<?php } ?>
    		 				
    		 				
    		 					
    		 		<?php } ?>
    		 		
    		 		});
    		 	</script>
    		 	
    	<?php return ob_get_clean();	 	
          }  
       
       /**
		* 	Less compiler
		*	Receives variables set in the interface
		* 	Processes them and writes the file to the media folder
		*/
       
        private function compile($content, $id, $target,$template, $name) {
	      
		      	// Variables and Settings
		      	 	$variables = $content['colors'];
		      	 	$settings = $content['settings'];
		      	 	$extra_files = $content['files'];
		      	 	$framework_version = $settings['framework_version'];
		      	 	$framework_enable = $settings['framework_enable'];
		      	 	$framework_files = $settings['framework_files'];
		      		$enable_template_css = $settings['template_css'];
		      		$theme = $settings['theme'];
		      		$theme = strtolower(str_replace(' ', '-', $theme));
		      		$fontawesome_type = $settings['font_awesome_type'];
		      		$addtocompiler = $extra_files['custom_less'];
		      		$compressed = $settings['compresscss'];
		      		$animate = $settings['animate'];
		      		$animations = $extra_files['animations'];
		      		$rowstyles = $extra_files['rowstyles'];
		      	
		      	    		
		      		
		      		/**
		      		 * Import LESS PHP
		      		 *
		      		 *
		      		 */
		      		
		      			require_once JPATH_ROOT . '/templates/'.$template.'/zengrid/libs/lessc/Less.php';
		      		
		      		
		      		
		      		/**
		      		 * Paths
		      		 *
		      		 *
		      		 */
		      			$sitepath = str_replace('\\', '/', ROOT_PATH);
		      			$lessBasePath = TEMPLATE_PATH . '/less';
		      			$relative_path = TEMPLATE_PATH_RELATIVE;
		      		
		      		
		      		/**
		      		 * 	When the load template.css is enabled we create the template.css based on the current settings
		      		 *	Lets just check to see if the template.css file exists early int he file
		      		 *	and return if it does to save some time.
		      		 */
		      		 	
		      		 	$write_css = 0;
		      		 	
		      			if($enable_template_css =="1") {
		      				if (file_exists(TEMPLATE_PATH . '/css/template.css')) {
		      				    echo 'Template.css is now enabled. Current theme styling will be bypassed.';
		      				    
		      				    return;
		      				    
		      				} else {
		      					$write_css = 1;
		      				}
		      			}
		      			
		      			
		      		
		      		// Only parse the admin settings if not creating css
		      		if(!$enable_template_css) {
		      		
		      		/**
		      		 * Variables
		      		 *
		      		 *
		      		 */
		      		 
		      		
		      			$variable_array = array();
		      	
		      			// Process Colour Variables
		      			foreach ($variables as $param =>$color) {
		      				
		      				
		      					if($color !=="") {
		      						// First three letters
		      						// Because Hex values like ddd and aaa
		      						// Also look like darken and auto
		      						$threeletters = substr($color, 0, 3);
		      						
		      						if($threeletters =="non") {
		      							
		      							$color = 'transparent';
		      						}
		      						
		      						$firstletter = substr($color, 0, 1);
		      										
		      						if (trim($color) !== '') {
		      							
		      							
		      							// Checks to see if using transparent, inherit, auto, lighten or darken
		      							if($firstletter =="@" || $firstletter =="l" || $firstletter =="t" || $firstletter =="i") {
		      								
		      								if($firstletter == "l") {
		      									
		      									// We can lighten # or variables so need to readd # if its a hex
		      									$variable = explode('(', $color);
		      									
		      									// Check for first letter of variable we are looking at
		      									$firstletter = substr($variable[1], 0, 1);
		      									
		      									// If its a variable just do the normal process
		      									if($firstletter == '@') {
		      										$color = $variable[0].'('.$variable[1];
		      									} else {
		      										//other wise it must be a colour so we add back the #
		      										$color = $variable[0].'(#'.$variable[1];
		      									}
		      								}
		      							
		      							} elseif($threeletters =="dar" || $threeletters =="aut") {
		      									
		      									// We can lighten # or variables so need to readd # if its a hex
		      									$variable = explode('(', $color);
		      									
		      									// Check for first letter of variable we are looking at
		      									$firstletter = substr($variable[1], 0, 1);
		      									
		      									// If its a variable just do the normal process
		      									if($firstletter == '@') {
		      										$color = $variable[0].'('.$variable[1];
		      									} else {
		      										//other wise it must be a colour so we add back the #
		      										$color = $variable[0].'(#'.$variable[1];
		      									}
		      									
		      									
		      							} elseif($threeletters =="rgb") {
		      							
		      								
		      							} else {
		      								$color = '#'.$color;
		      							}
		      							
		      						
		      							$variable_array[$param] = $color;
		      						}
		      	
		      					}		
		      			}
		      			
		      			
		      		
		      			// Process relevant settings
		      			foreach ($settings as $name => $setting) {
		      		
		      				if($setting!=="") {
		      					$variable_array[$name] = $setting;
		      				}
		      			}
		      	
		      	
		      	
		      			// Adds path variable for when including nested images in styles folder
		      			$variable_array['@path'] = "'../../'";
		      	
		      		
		      		/**
		      		 * Declare Files Array
		      		 *
		      		 *
		      		 */
		      		
		      		
		      			$files = array();
		      		
		      		
		      		
		      		
		      		/**
		      		 * Bootstrap
		      		 *
		      		 *
		      		 */
		      		
		      		 if($framework_enable) {
		      		
		      		 	// Get Bootstrap files to compile
		      		 	$framework_files = explode('_', $framework_files);
		      		
		      		 	$write_framework_file = "";
		      		 	$write_framework_file .= '@import "variables.less";'."\n";
		      		
		      		 	if($framework_version !=="uikit") {
		      		 		$write_framework_file .= '@import "mixins.less";'."\n";
		      		 	}
		      		
		      		 	foreach ($framework_files as $key => $file) {
		      		 		if($file !=="") {
		      		 			$write_framework_file .= '@import "'.$file.'.less";'."\n";
		      		 		}
		      		 	}
		      		
		      		 	file_put_contents(TEMPLATE_PATH.'/zengrid/libs/frameworks/'.$framework_version.'/less/'.$framework_version.'.less', $write_framework_file);
		      		
		      		 	$files[] =  '../zengrid/libs/frameworks/'.$framework_version.'/less/'.$framework_version.'.less';
		      		 }
		      		
		      		
		      		
		      			
		      		
		      		
		      		/**
		      		 * Main Template
		      		 *
		      		 *
		      		 */
		      		
		      		// Main Theme less File
		      		$files[] = 'template.less';
		      		
		      		
		      		/**
		      		 * Animate CSS
		      		 *
		      		 *
		      		 */
		      		
		      		// Animate css
		      		if($animate) {
		      			
		      			$files[] = '../zengrid/libs/zengrid/less/animate/animate.less';
		      			
		      			$animations = explode(',', $animations);
		      			$animations = array_unique($animations);
		      			
		      			foreach ($animations as $key => $animation) {
		      				if($animation !=="" && $animation !=="none") {
		      					$files[] =  '../zengrid/libs/zengrid/less/animate/'.$animation.'.less';
		      				}	
		      			}
		      		}	
		      		
		      		
		      		
		      		/**
		      		 * Font Awesome
		      		 *
		      		 *
		      		 */
		      		
		      			if($fontawesome_type) {
		      				$files[] =  '../zengrid/libs/zengrid/less/fontawesome/font-awesome-'.$fontawesome_type.'.less';
		      			}
		      				
		      		
		      		
		      		/**
		      		 * Row Styles CSS
		      		 *
		      		 *
		      		 */
		      		
		      			if(is_array($rowstyles)) {
		      				$rowstyles = array_filter($rowstyles);
		      				$row_styles = TEMPLATE_PATH . '/less/styles';
		      				$row_path = 'styles';
		      				if(is_dir($row_styles)) {
		      					
		      					foreach ($rowstyles as $key => $row_file) {
		      					
		      						$files[] = $row_path.'/'.$row_file.'.less';
		      							
		      					}
		      				}	
		      			
		      			}
		      			
		      			
		      		
		      		/**
		      		 * Extra Less files added
		      		 *
		      		 *
		      		 */
		      		
		      			
		      		
		      			if($addtocompiler !=="") {
		      				$addtocompiler = rtrim($addtocompiler, ",");
		      				$addtocompiler = explode(',', $addtocompiler);
		      				
		      				foreach ($addtocompiler as $key => $file) {
		      					$files[] = $file;
		      				}
		      				
		      			}
		      			
		      			
		      		
		      		/**
		      		 * Custom Less File
		      		 *
		      		 *
		      		 */
		      		
		      			$customless = TEMPLATE_PATH . '/less/custom.less';
		      			    
		      	        if(file_exists($customless)) {
		      	            $files[] = 'custom.less';
		      	        }
		      		
		      		
		      		
		      		
		      		/**
		      		 * Create generated template.less file to parse
		      		 *
		      		 *
		      		 */
		      		
		      		$files_to_compile = '// This file is automatically generated by the Zen Grid Framework. Do not edit';
		      		$files_to_compile .= "\n";
		      		$files_to_compile .= "\n";
		      		
		      		foreach ($files as $key => $file) {
		      			if($file !=="") {
		      				$files_to_compile .= '@import "';
		      				$files_to_compile .= $file;
		      				$files_to_compile .= '";';
		      				$files_to_compile .= "\n";
		      			}
		      		}
		      		
		      		print_r($files);
		      		file_put_contents($lessBasePath.'/template-generated.less', $files_to_compile);
		      		
		      		
		      		/**
		      		 * Start Compression and Loop through the array
		      		 *
		      		 *
		      		 */
		      	
		      			$options = array(
		      			    'sourceMap'         => true,
		      			    'sourceMapWriteTo'  => TEMPLATE_PATH . 'css/theme.'.$theme.'.map',
		      			    'sourceMapURL'      => $relative_path.'/css/theme.'.$theme.'.map',
		      			   	'sourceMapRootpath'	=> '../',
		      			   	'sourceMapBasepath'   => str_replace('\\', '/', ROOT_PATH) . '/'.TEMPLATE_PATH_RELATIVE
		      			);
		      			
		      			if($compressed) {
		      				$options['compress'] = 'true';
		      			}
		      			
		      			$parser = new Less_Parser( $options );
		      			$parser->parseFile( TEMPLATE_PATH . 'less/template-generated.less', '');
		      			$parser->ModifyVars($variable_array);
		      			$css = $parser->getCss();
		      		
		      	
		      			file_put_contents(TEMPLATE_PATH . 'css/theme.'.$theme.'.css', $css);
		      			
		      			
		      			
		      			// Create gzipped version
		      				$gzip = '<?php ob_start ("ob_gzhandler");
		      				    header("Content-type: text/css; charset: UTF-8");
		      				    header("Cache-Control: must-revalidate");
		      				    $offset = 60 * 60 ;
		      				    $ExpStr = "Expires: " .
		      				    gmdate("D, d M Y H:i:s",
		      				    time() + $offset) . " GMT";
		      				    header($ExpStr);?>';
		      			
		      				$gzip .= $css;
		      			
		      				file_put_contents(TEMPLATE_PATH . 'css/theme.'.$theme.'.php', $gzip);
		      			
		      			echo 'compiled';
		      			
		      			
		      		}
		      			
		      			
		      			/**
		      			 * 	 
		      			 *	If creating template.css for first time
		      			 *
		      			 */
		      			 
		      			 
		      			if($enable_template_css =="1" && $write_css) {
		      			
		      				
		      				
		      					// Main Theme less File
		      					$files[] = $lessBasePath.'/template.less';
		      					
		      					
		      					/**
		      					 * Start Compression and Loop through the array
		      					 *
		      					 *
		      					 */
		      				
		      									
		      					$options = array(
		      					    'sourceMap'         => true,
		      					    'sourceMapWriteTo'  => TEMPLATE_PATH . 'css/theme.'.$theme.'.map'
		      					);
		      				
		      					if($compressed) {
		      						$options['compress'] = 'true';
		      					}
		      				
		      					$parser = new Less_Parser( $options );
		      				
		      					$css = "";
		      					foreach ($files as $key => $file) {
		      					
		      						
		      						$parser->parseFile( $file,'');
		      						$parser->ModifyVars($variable_array);
		      						$css .= $parser->getCss();
		      						$parser->reset();
		      					}
		      					
		      					
		      					
		      			    file_put_contents(TEMPLATE_PATH . 'css/template.css', $css);
		      			    
		      			   echo  'compiled';
		      			   
		      			} 
		}
       
       
       
        /**
		* 	JS Compressor
		*	Receives variables set in the interface
		* 	Processes them and writes the file
		*/
		
       private function compress($data, $id,$target, $template, $name) {
	       	
	       	$extrafiles = $data['files'];
	       	$settings = $data['settings'];
	       	$extrafiles = array_filter($extrafiles);
	       	
	       
	       	$page_type = $id;
	       	$animations = $settings['animations'];
	       	
	       	
	       	// Import Compile Library
	       	require_once JPATH_ROOT . '/templates/'.$template.'/zengrid/libs/jshrink/minifier.php';
	       	
	       	
	       	
	       	// Create a single array combining the extra files
	       	// And the assets.xml
	       	// We added the extra files first so then users can add
	       	// Any relevant scripts to the js.script.js file that gets loaded afterwards
	       	
	       	$files = array();
	       	
	       	foreach ($extrafiles as $key => $file) {
	       		$files[] = $file;
	       	}
	       	
	       	$assets = (array)self::getassets('../');
	       	
	       	foreach ($assets as $key => $asset) {
	       		$files[] = $asset;
	       	}
	       	
	       	// Animations
	       	if($animations) {
	       		$files[] = '../zengrid/libs/zengrid/js/wow.min.js';
	       	}
	       	
	       	
	       	$path = TEMPLATE_PATH.'/js/';
	       	
	       	$buffer = "";
	       	
	       	foreach ($files as $key => $file) {
	       		if($file !=="") {
	       			$buffer .= file_get_contents($path . $file) . "\n";	
	       		}
	       	}
	       	
	       	
	       	// Minify all the scripts
	       	$minifiedCode = \JShrink\Minifier::minify($buffer, array('flaggedComments' => true));
	       	
	       	// Write the js file
	       	file_put_contents(TEMPLATE_PATH . 'js/template-'.$page_type.'.js', $minifiedCode);
	       	
	       	
	       	// Create gzipped version
	       	$gzip = '<?php ob_start ("ob_gzhandler");
	       	    header("Content-type: application/js; charset: UTF-8");
	       	    header("Cache-Control: must-revalidate");
	       	    $offset = 60 * 60 ;
	       	    $ExpStr = "Expires: " .
	       	    gmdate("D, d M Y H:i:s",
	       	    time() + $offset) . " GMT";
	       	    header($ExpStr);?>';
	       	
	       	$gzip .= $minifiedCode;
	       	
	       	// Write the compressed file
	       	file_put_contents(TEMPLATE_PATH . 'js/template-'.$page_type.'.php', $gzip);
	       		
       }
       
       /**
        * 	Gets JS files listed in the assets file
        *	
        *
        */
        
       public function getassets($path = "") {
       		$assets = TEMPLATE_PATH.'/settings/assets.xml';
       		$assets = simplexml_load_file($assets);
       		$assets = $assets->js->file;
       	return $assets;
       }
       
  
}


?>