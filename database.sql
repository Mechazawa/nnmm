/*                                                                              
 * ---------------------------------------------------------------------------- 
 * "THE BEER-WARE LICENSE" (Revision 42):                                       
 * <Mega{at}IOException.at> wrote this file. As long as you retain this notice  
 * you can do whatever you want with this stuff. If we meet some day, and you   
 * think this stuff is worth it, you can buy me a beer in return                
 * ---------------------------------------------------------------------------- 
 */                                                                             
                                                                                
CREATE TABLE `pastes` (                                                         
  `id` varchar(6) NOT NULL,                                                     
  `data` longtext,                                                              
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMES
TAMP,                                                                           
  PRIMARY KEY (`id`)                                                            
); 
