<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Ajax.Zengridframework
 * @author      Joomla Bamboo - design@joomlabamboo.com
 * @copyright   Copyright (c) 2016 Joomla Bamboo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0
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
  

			if(phpversion() > "5.4") {
				$content = json_encode($data, JSON_UNESCAPED_UNICODE);
			} else {
				$content = json_encode($data);
			}
			
			$content = self::indent($content);
			
			//$content = json_encode($data, JSON_PRETTY_PRINT);
			$path = JPATH_ROOT .'/templates/'.$template.'/settings/'.$target.'/';
       		
       		if($target =="config") {
       			$name = 'config-'.$id;
       		}
       		$fileName = $path . $name . '.json';
       		
       		
       		JFile::write($fileName, $content);
       }
       
       
       
       /**
        * Indents a flat JSON string to make it more human-readable.
        *
        * @param string $json The original JSON string to process.
        *
        * @return string Indented version of the original JSON string.
        */
       function indent($json) {
       
           $result      = '';
           $pos         = 0;
           $strLen      = strlen($json);
           $indentStr   = '  ';
           $newLine     = "\n";
           $prevChar    = '';
           $outOfQuotes = true;
       
           for ($i=0; $i<=$strLen; $i++) {
       
               // Grab the next character in the string.
               $char = substr($json, $i, 1);
       
               // Are we inside a quoted string?
               if ($char == '"' && $prevChar != '\\') {
                   $outOfQuotes = !$outOfQuotes;
       
               // If this character is the end of an element,
               // output a new line and indent the next line.
               } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                   $result .= $newLine;
                   $pos --;
                   for ($j=0; $j<$pos; $j++) {
                       $result .= $indentStr;
                   }
               }
       
               // Add the character to the result string.
               $result .= $char;
       
               // If the last character was the beginning of an element,
               // output a new line and indent the next line.
               if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                   $result .= $newLine;
                   if ($char == '{' || $char == '[') {
                       $pos ++;
                   }
       
                   for ($j = 0; $j < $pos; $j++) {
                       $result .= $indentStr;
                   }
               }
       
               $prevChar = $char;
           }
       
           return $result;
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
    		 	$positions = $_POST['content'];
    		 	$positions = explode('.', $positions);
    		 	$positions = $positions[0];
    		 	
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
    		 		$default_layout = $zgf->get_json('settings/positions.json');
    		 	}
    		 	
    		 
    		 	foreach ($default_layout as $key => $row) { 
    		 		
    		 			
    		 			echo '<div id="'.$key.'-row" data-row="'.$key.'-row" class="resize-row module-row connectedSortable">';
    		 			echo '<span class="uk-badge">'.$key.'</span>';
    		 			echo '<div class="edit-row '.$key.'_edit-row">
    		 			<span class="icon-pencil"></span></div>';
    		 			
    		 			// Used positions
	 					$layout = 	(array)$theme_layout->$key->positions;
	 					unset($layout['null']);
	 						
	 					// Default main positions
	 					$default = (array)$default_layout->$key;
	 					$diff = array_diff_key($default,$layout);
	 					
	 					// Items used
	 					foreach ($layout as $title => $width) {
	 					
	 						echo '<div id="'.$title.'" class="resizable ui-widget-content grid-'.$width.'" data-width="'.$width.'" data-active="0" style="display:nosne">';
	 						echo '<h3 class="ui-widget-header">';
	 						echo '<span class="icon-eye"></span>';
	 						echo '<span class="col-count"></span>'.ucfirst($title).' </h3>';
	 						echo '</div>';
	 						
	 						echo '<script>';
	 						echo 'jQuery(document).ready(function($) {';
	 						echo '$(".unused-modules [data-id=\"'.$title.'\"]").hide();';
	 						echo '});</script>';
	 					}
	 					
	 					// Items in the positions.json
	 					foreach ($diff as $title => $width) {
	 				
    		 				if(isset($layout->{$title})) {
    		 					$width = $layout->{$title};
    		 				}
		 				
    		 				echo '<div id="'.$title.'" class="resizable ui-widget-content grid-'.$width.'" data-width="'.$width.'" data-active="0" style="display:none">';
    		 				echo '<h3 class="ui-widget-header">';
    		 				echo '<span class="icon-eye"></span>';
    		 				echo '<span class="col-count"></span>'.ucfirst($title).' </h3>';
    		 				echo '</div>';
	 					}
    		 			
    		 		echo '</div>';
    		 			
    		 		
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
	    		 					$("#resize-container [data-row='<?php echo $key;?>-row'] #<?php echo $module;?>").attr("data-active", "1").show();
	    		 					$("#resize-container .unused-modules [data-id='<?php echo $module;?>']").hide();
	    		 				<?php }
    		 			 	} 
    		 			} ?>
    		 		
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
		      	 	$framework_version = null;
		      	 	$framework_enable = null;
		      	 	$framework_files = "";
		      	 	$enable_template_css = 0;
		      	 	$compressed = 0;
		      	 	$animate = 0;
		      	 	$animations = "";
		      	 	
		      	 	if(isset($settings['framework_version'])) {
		      	 		$framework_version = $settings['framework_version'];
		      	 	}
		      	 	
		      	 	if(isset($settings['framework_enable'])) {
		      	 		$framework_enable = $settings['framework_enable'];
		      	 	} 
		      	 	
		      	 	if(isset($extra_files['framework_files'])) {
		      	 		$framework_files = $extra_files['framework_files'];
		      	 	}
		      	 	
		      	 	if(isset($settings['template_css'])) {
		      	 		$enable_template_css = $settings['template_css'];
		      	 	}
		      	 	
		      	 	if(isset($settings['compresscss'])) {
		      	 		$compressed = $settings['compresscss'];
		      	 	}
		      	 	
		      	 	if(isset($settings['animate'])) {
		      	 		$animate = $settings['animate'];
		      	 	}
		      	 	
		      	 	if(isset($extra_files['animations'])) {
		      	 		$animations = $extra_files['animations'];
		      	 	}
		      	 	
		      	 	if(isset($extra_files['child'])) {
		      	 		$child = $extra_files['child'];
		      	 	}
		      	 	
		      		$theme = $settings['theme'];
		      		$theme = strtolower(str_replace(' ', '-', $theme));
		      		$fontawesome_type = $settings['font_awesome_type'];
		      		$addtocompiler = $extra_files['custom_less'];
		      		
		      		
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
		      									
		      									if(isset($variable[1])) {
			      									// Check for first letter of variable we are looking at
			      									$firstletter = substr($variable[1], 0, 1);
			      									
			      									// If its a variable just do the normal process
			      									if($firstletter == '@') {
			      										$color = $variable[0].'('.$variable[1];
			      									} else {
			      										//other wise it must be a colour so we add back the #
			      										$color = $variable[0].'(#'.$variable[1];
			      									}
			      								} else {
			      								//	print_r($variable[0]);
			      								}
		      								} else if($firstletter == "l") {
		      									$color = "transparent";
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
		      							
		      								
		      							} elseif($threeletters =="fad") {
		      								
		      									$color = str_replace('fade(', 'fade(#', $color);
		      									$color = str_replace('#@', '@', $color);
		      										
		      								}
		      									else {
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
		      				if($animation !=="" && $animation !=="none" && $animation !=="null") {
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
	      			 * Child themes
	      			 *
	      			 *
	      			 */
	      			
	      				
	      			if(isset($child)) {
	      				if($child !=="none" && $child !=="") {
	      					if(file_exists(TEMPLATE_PATH.'child/'.$child.'/'.$child.'.less')) {
	      						$files[] = '../child/'.$child.'/'.$child.'.less';
	      					}
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
		      		
			  		//	print_r($files);
		      		file_put_contents($lessBasePath.'/template-generated.less', $files_to_compile);
		      		
		      		
		      		/**
		      		 * Dumps template variables for later use in devmode
		      		 *
		      		 *
		      		 */
		      		
		      		$variable_string = '// This file is automatically generated by the Zen Grid Framework. Do not edit';
		      		$variable_string .= "\n";
		      		$variable_string .= "\n";
		      		
		      		foreach($variable_array as $param => $value) {
				  			$variable_string .= '@'.str_replace('@', '', $param).':'.$value.";\n";
			      	}
		      		
		      		file_put_contents($lessBasePath.'/variables-generated-devmode.less', $variable_string);
		      		

		      		
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
			$child = $data['child'];
	       
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
	       	
	       	$assets = self::getassets();
	  
	       	foreach ($assets as $key => $asset) {
	       		$files[] = $asset;
	       	}
	       	
	       	// Animations
	       	if($animations) {
	       		$files[] = '../zengrid/libs/zengrid/js/wow.min.js';
	       	}
	       	
	       	// Child
	       	if(isset($child)) {
	       		if($child !=="none" && $child !=="") {
	       			$files[] = '../child/'.$child.'/'.$child.'.js';
	       		}
	       	}
	       		
	       	
	       	$path = TEMPLATE_PATH.'/js/';
	       	
	       	$buffer = "";
	       	
	       	 foreach ($files as $key => $file) {
	       		if($file !=="") {
	       			if(file_exists($path . $file)) {
	       				$buffer .= file_get_contents($path . $file) . "\n";	
	       			}
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
	       	$assets = '../templates/buildr/settings/assets.xml';
	
	       	$assets = simplexml_load_file($assets);
	       	$assets = $assets->js->file;
	       	$files = array();
	       	
	       	foreach($assets as $asset) {
	       		$files[] = $asset;
	       	}
	      
	       	return $files;
       }
       
  
}


?>