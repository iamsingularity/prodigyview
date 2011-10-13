<?php 
/*
*Copyright 2011 ProdigyView LLC. All rights reserved.
*
*Redistribution and use in source and binary forms, with or without modification, are
*permitted provided that the following conditions are met:
*
*   1. Redistributions of source code must retain the above copyright notice, this list of
*      conditions and the following disclaimer.
*
*   2. Redistributions in binary form must reproduce the above copyright notice, this list
*      of conditions and the following disclaimer in the documentation and/or other materials
*      provided with the distribution.
*
*THIS SOFTWARE IS PROVIDED BY ProdigyView LLC ``AS IS'' AND ANY EXPRESS OR IMPLIED
*WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
*FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL ProdigyView LLC OR
*CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
*CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
*SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
*ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
*NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
*ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
*The views and conclusions contained in the software and documentation are those of the
*authors and should not be interpreted as representing official policies, either expressed
*or implied, of ProdigyView LLC.
*/
class PVUsers extends PVStaticObject {
	
	
	/**
	 * Check if the user has a current active session
	 * 
	 * @return boolean logged_in: True if logged in, else false
	 */
	public static function checkLogin(){
		if(PVSession::readCookie('pv_username')){
			return true;
		}
		else if(PVSession::readSession('pv_username')){
			return true;
		}
		return false;
	}//endcheckLogin
	
	/**
	 * Check if the user has a current active session
	 * 
	 * @return boolean logged_in: True if logged in, else false
	 */
	public static function getUserID(){
		if(PVSession::readCookie('pv_userid')){
			return PVSession::readCookie('pv_userid');	
		}
		else if(PVSession::readSession('pv_userid')){
			return PVSession::readSession('pv_userid');
		}
		return false;
	}//end getUserID
	
	
	public static function getUserName(){
		if(PVSession::readCookie('pv_username')){
			return PVSession::readCookie('pv_username');
		}
		else if(PVSession::readSession('pv_username')){
			return PVSession::readSession('pv_username');
		}
		return false;
	}//end getUserID
	
	public static function getUserEmail(){
		if(PVSession::readCookie('pv_useremail')) {
			return PVSession::readCookie('pv_useremail');	
		}
		else if(PVSession::readSession('pv_useremail')){
			return PVSession::readSession('pv_useremail');	
		}
		return false;
	}//end getUserID
	
	public static function getUserRole(){
		if(PVSession::readCookie('pv_roles')){
			return PVSession::readCookie('pv_roles');
		}
		else if(PVSession::readSession('pv_roles')){
			return PVSession::readSession('pv_roles');
		}
		return false;
	}//end getUserRole
	
	
	public static function getUserAccessLevel(){
		if(PVSession::readCookie('pv_access_level')){
			return PVSession::readCookie('pv_access_level');
		}
		else if(PVSession::readSession('pv_access_level')){
			return PVSession::readSession('pv_access_level');
		}
		return false;
	}//end getUserRole
	
	/**
	 * Used for quick retrieval of roles that are assigned to a user
	 * based on the user's id.
	 * 
	 * @param string $user_id The id of the user whose roles to retrieve
	 * 
	 * @return array $roles The id and name of the roles
	 * @access public
	 */
	public static function getAssignedUserRoles($user_id){
		
		$user_id=PVDatabase::makeSafe($user_id);
		if(PVValidator::isID($user_id)){
			$query="SELECT * FROM ".PVDatabase::getUserRolesRelationsTableName()." JOIN ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesRelationsTableName().".role_id=".PVDatabase::getUserRolesTableName().".role_id WHERE user_id='$user_id'";
		}
		else{
			$query="SELECT * FROM ".PVDatabase::getUserRolesRelationsTableName()." JOIN ".PVDatabase::getUsersTableName()." ON ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id JOIN ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesRelationsTableName().".role_id=".PVDatabase::getUserRolesTableName().".role_id WHERE user_email='$user_id'";
		}
		
		$result=PVDatabase::query($query);
		
		$roles=array();
		while($row=PVDatabase::fetchArray($result)){
			$roles[]=$row;
		}//end while
		
		return $roles;
	}//end getUserRoles
	
	public static function getUserRoleType(){
		if(isset($_COOKIE['pv_role_type'])){
			return $_COOKIE['pv_role_type'];	
		}
		else if(isset($_SESSION['pv_role_type'])){
			return $_SESSION['pv_role_type'];
		}
		else{
			return 0;	
		}
		
	}//end getUserRoleType
	
	/**
	 * Validates a user based upon the creditenials of a login and password.
	 * 
	 * @param string $username Can either be the username or password of a user
	 * @param string $password The password associated with the username
	 * @param boolean $save_cookie Sets a cookie with the user's information on login
	 * @param boolean $password_encoded Will hash the password to MD5 if set to false.
	 * 
	 * @return boolean $success Returns true if the login was a success
	 * @access public
	 */
	public static function attemptLogin($username, $password, $save_cookie=TRUE, $password_encoded=false) {
		
		$username=PVDatabase::makeSafe($username);
		$password=PVDatabase::makeSafe($password);
		
		if(PVValidator::isID($username)){
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." WHERE user_id='$username'";
		} else if(PVValidator::isValidEmail($username)) {
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." WHERE user_email='$username'";
		} else {
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." WHERE username='$username'";
		}
		
		$result =PVDatabase::query($query);
		
        if( PVDatabase::resultRowCount($result) > 0) {

			$row = PVDatabase::fetchArray($result);
			$user_id=$row['user_id'];
			$dbpassword=$row['user_password'];
			$username=$row['username'];
			$email=$row['user_email'];
			$user_access_level=$row['user_access_level'];
			
			$row=self::_applyFilter( get_class(), 'pre_'.__FUNCTION__ , $row , $row);
			
			if(self::comparePasswords($password, $dbpassword, $password_encoded)) {
					
				$row=self::_applyFilter( get_class(), 'post_'.__FUNCTION__ , $row , $row);
				$roles=self::getAssignedUserRoles($user_id);
				self::setUserSession($user_id, $username, $email , $roles, $user_access_level );
				if($save_cookie) {
					self::setUserCookies($user_id, $username, $email , $roles, $user_access_level );
				}
				return TRUE;
			}
		}
		return FALSE;
	}//end attemptLogin
	
	/**
	 * Adds a user to the database with the passed information. The only actual required
	 * information is the user's email.
	 * 
	 * @param array $args The passed values in an array that define a user.
	 * 			-'user_email' _string_: The email address of the user. Can be used for logging in with password
	 * 			-'username' _string_: The user's name. Can be used for logging in with password.
	 * 			-'user_access_level' _int_: The user's level access that can be used with a security settings
	 * 			-'user_password' _string_: The password the user. Will be MD5 hashed unless other hashing algorithim is defined
	 * 			-'activation_code' _string_: The code that will be used for activating an account. Will be auto-generated if not defined
	 * 			-'registration_date' _date/time_: Records the date/time a user has registerd. Will default to current timestamp of not defined
	 * 			-'user_role' _mixed_: Can either be a role passed as a string(role name) or id(role_id) or an array of role_ids or role_names
	 * @param boolean $password_encoded If set to false, password will be md5 hashed. Otherwise password is saved as passed.
	 *  
	 * @return int $user_id Returns the id of thew newly created user
	 * @access public
	 */
	public static function addUser($args=array(), $password_encoded=false){
		$args += self::getUserDefaults();
		$args = PVDatabase::makeSafe($args);
		extract($args);
		if(!empty($user_email)){
			$user_email=strtolower($user_email);
			
			if(empty($username)){
				$username=$user_email;
			}
			
			if(self::getUserIDByEmail($user_email)==0){
				
				$user_access_level=ceil($user_access_level);
				
				if(empty($user_password)){
					$user_password=PVDatabase::makeSafe(PVTools::generateRandomString());
				}
				
				if($password_encoded==false){
					$user_password=MD5($user_password);
					$user_password=PVDatabase::makeSafe($user_password);
				}
				
				if(empty($registration_date)){
					$registration_date=date("Y-m-d H:i:s", time()) ;	
				}
				
				if(empty($activation_code)){
					$activation_code=PVDatabase::makeSafe(PVTools::generateRandomString());
				}
				
				if(empty($activation_date)){
					$activation_date=date("Y-m-d H:i:s", time()) ;	
				}
			
				$query="INSERT INTO ".PVDatabase::getUsersTableName()."(user_email, user_password, is_active, username, receive_html_emails, user_image, user_access_level) VALUES( '$user_email' , '$user_password', '$is_active', '$username', '$receive_html_emails' , '$user_image', '$user_access_level')";
				
				$user_id=PVDatabase::return_last_insert_query($query, 'user_id', PVDatabase::getUsersTableName() );
				
				$query="INSERT INTO ".PVDatabase::getUserActivationTableName()."( user_id ,  activation_code) VALUES('$user_id', '$activation_code' )";
				PVDatabase::query($query);
				
				if(is_array($user_role)){
					
					foreach($user_role as $key=>$role_value){
						
						if(!PVValidator::isInteger($role_value)){
							$tmp_role=self::getUserRoleByName($role_value);
							$role_value=$tmp_role['role_id'];
						}
						$query="INSERT INTO ".PVDatabase::getUserRolesRelationsTableName()."(role_id, user_id) VALUES('$role_value', '$user_id')";
						PVDatabase::query($query);
					}//end foreach
					
				} else if(!empty($user_role)) {
					if(!PVValidator::isInteger($user_role)){
						$tmp_role=self::getUserRoleByName($user_role);
						$user_role=$tmp_role['role_id'];
					}
					$query="INSERT INTO ".PVDatabase::getUserRolesRelationsTableName()."(role_id, user_id) VALUES('$user_role', '$user_id')";
					PVDatabase::query($query);
				}
				
				return $user_id;
			} 

			return false;
		}//end if user email not empty
	}
	
	/**
	 * Updates a user's data based upoon the id of the user. The sam
	 * 
	 * @see addUser. Same parameters can be passed 
	 */
	public static function updateUser($args=array()){
		$args += self::getUserDefaults();
		$args = PVDatabase::makeSafe($args);
		extract($args);
		
		if(empty($registration_date)){
			$registration_date=date("Y-m-d H:i:s", time()) ;	
		}
			
		if(empty($activation_date)){
			$activation_date=date("Y-m-d H:i:s", time()) ;	
		}
			
		$user_access_level=ceil($user_access_level);
			
		$query="UPDATE ".PVDatabase::getUsersTableName()." SET user_email='$user_email', is_active='$is_active', username='$username', receive_html_emails='$receive_html_emails', registration_date='$registration_date', activation_date='$activation_date', user_image='$user_image', user_image_thumb='$user_image_thumb', user_access_level='$user_access_level' WHERE user_id='$user_id' ";
		PVDatabase::query($query);
	}//end  update User
	
	public static function updateUserPassword($args=array(), $encrypted=FALSE){
		
		if(is_array($args) && !empty($args['user_id'])){
			$args=PVDatabase::makeSafe($args);
			extract($args);
			
			if(!$encrypted){
				$user_password=md5($user_password);
			}
			
			$query="UPDATE ".PVDatabase::getUsersTableName()." SET user_password='$user_password' WHERE user_id='$user_id' ";
			
			PVDatabase::query($query);
		}
		
	}//end  update User
	
	public static function generateResetCode($user_id){
		
		$user_info=self::getUserInfo($user_id);
		
		if(!empty($user_info)){
			
			$user_id=$user_info['user_id'];
			$reset_code=PVTools::generateRandomString(20);	
			
			$query="UPDATE ".PVDatabase::getUserActivationTableName()." SET reset_code='$reset_code' WHERE user_id='$user_id'";
			PVDatabase::query($query);
			
			return $reset_code;
		}
		
	}//end generateResetCode
	
	
	/**
	 * Adds a user to a role. The should be created first.
	 * 
	 * @param id $user_id The id of the user to add the role to.
	 * @param mixed $role_id The id of the role to add the user too or the name of the role. id is a better option if
	 * 		  there are multiple roles with the same name
	 * 
	 * @return boolean $success Returns true if the user was added, otherwise false.
	 * @access public
	 */
	public static function addUserToRole($user_id, $role_id){
		
		if(!PVValidator::isID($role_id) && !empty($role_id)){
				$role=self::getUserRoleByName($role_id);
				$role_id=$role['role_id'];
		}
			
		if(!empty($user_id) && !empty($role_id)){
			
			$user_id=PVDatabase::makeSafe($user_id);
			$role_id=PVDatabase::makeSafe($role_id);
			
			$query="SELECT * FROM ".PVDatabase::getUserRolesRelationsTableName()." WHERE user_id='$user_id' AND role_id='$role_id' ";
			$result = PVDatabase::query($query);
			
			if(PVDatabase::resultRowCount($result)<=0) {
				$query="INSERT INTO ".PVDatabase::getUserRolesRelationsTableName()."(user_id, role_id) VALUES('$user_id', '$role_id')";
				PVDatabase::query($query);	
				return true;
			}
		}//end
		
		return false;
	}//end addUserTRole
	
	/**
	 * Removes a user from a role.
	 * 
	 * @param id $user_id The id of user to remove from the role.
	 * @param mixed $role_id Either the name of the role or the id of the role.
	 * 			Be aware that if there are multipe rows with the same name, using the
	 * 			role name feature will delete the first one retrieved
	 * 
	 * @return void
	 * @access public
	 */
	public static function removeUserFromeRole($user_id, $role_id){
		
		if(!PVValidator::isID($role_id) && !empty($role_id)){
				$role=self::getUserRoleByName($role_id);
				$role_id=$role['role_id'];
		}
			
		if(!empty($user_id) && !empty($role_id)){
			
			$user_id=PVDatabase::makeSafe($user_id);
			$role_id=PVDatabase::makeSafe($role_id);
			
			$query="DELETE FROM ".PVDatabase::getUserRolesRelationsTableName()." WHERE user_id='$user_id' AND role_id='$role_id'";
			PVDatabase::query($query);
		}//end
		
	}//end removeUserFromRole
	
	/**
	 * Delete a user from the database and also delete associated content with that user.
	 * 
	 * @param int $user_id The id of the user to be removed
	 * @param array $options A list of options to delete content associated with the user
	 * 			-'remove_user_content' _boolean_: Default is true. Will remove any content associated with the user's id
	 * 			-'remove_user_comments' _boolean_: Default is true. Will remove any comments associated with the user's id
	 * 			-'remove_user_subscriptions' _boolean_: Default is true. Will remove any subscriptions associated with the user's id
	 * 			-'remove_user_points' _boolean_: Default is true. Will remove any points associated with the user's id
	 * 			-'remove_user_categories' _boolean_: Default is true. Will remove any categories associated with the user's id
	 * 			-'remove_user_options' _boolean_: Default is true. Will remove any options associated with the user's id
	 * 			-'remove_user_mutli_author' _boolean_: Default is true. Will remove multi_author status associated with the user's id
	 * 
	 * @return void
	 * @access public
	 */
	public static function deleteUser($user_id, $options=array()){
		$defaults=array(
			'remove_user_content'=>TRUE,
			'remove_user_comments'=>TRUE,
			'remove_user_subscriptions'=>TRUE,
			'remove_user_points'=>TRUE,
			'remove_user_categories'=>TRUE,
			'remove_user_options'=>TRUE,
			'remove_user_multi_author'=>TRUE
		);
		
		$options += $defaults;
		extract($options);
		
		if(!empty($user_id)){
			
			$user_id=PVDatabase::makeSafe($user_id);
			
			if($remove_user_content){
				$user_content_list=PVContent::getContentList(array('owner_id'=>$user_id));
				
				foreach($user_content_list as $key=>$value){
					PVContent::deleteContent($value['content_id']);
				}//end foreach
			}
			
			if($remove_user_comments){
				$user_comment_list=PVComments::getCommentList(array('owner_id'=>$user_id));
				
				foreach($user_comment_list as $key=>$value){
					PVComments::deleteComment($value['comment_id']);
				}//end foreach
			}
			
			if($remove_user_subscriptions){
				$user_subscription_list=PVSubscriptions::getSubscriptionList(array('user_id'=>$user_id));
				
				foreach($user_subscription_list as $key=>$value){
					PVSubscriptions::deleteUserSubscription($value['subscription_id']);
				}//end foreach
			}
			
			if($remove_user_points){
				$user_point_list=PVPoints::getPointsList(array('user_id'=>$user_id));
				
				foreach($user_point_list as $key=>$value){
					PVPoints::deleteUserPoint($value['point_id']);
				}//end foreach
			}
			
			if($remove_user_categories){
				$user_category_list=PVContent::getCategoryList(array('category_owner'=>$user_id));
				
				foreach($user_category_list as $key=>$value){
					PVContent::deleteCategory($value['cateogry_id']);
				}//end foreach
			}
			
			if($remove_user_options){
				$user_option_list=PVTools::getOptionList(array('user_id'=>$user_id));
				
				foreach($user_option_list as $key=>$value){
					PVTools::deleteOption($value['option_id']);
				}//end foreach
			}
			
			if($remove_user_multi_author){
				$query="DELETE FROM ".PVDatabase::getContentMultiAuthorTableName()." WHERE author_id='$user_id'";
	    		PVDatabase::query($query);
			}

			$query="DELETE FROM ".PVDatabase::getUsersTableName()." WHERE user_id='$user_id'";
			PVDatabase::query($query);
			
			$query="DELETE FROM ".PVDatabase::getUserRolesRelationsTableName()." WHERE user_id='$user_id'";
			PVDatabase::query($query);
			
		}//end not empty
	}//end deleteUser
	
	
	
	public static function updateUserFields($args){
		
		if(!empty($args['user_id']) && is_array($args) ){
			
			$UPDATE_CLAUSE='';
			$first=1;
			foreach($args as $key=>$value ){
				
				if($key!='user_id'){
					if($first==0){
						$UPDATE_CLAUSE.=' , ';
					}
					$UPDATE_CLAUSE.=" $key='".PVDatabase::makeSafe($value)."' ";
					$first=0;
				}
				
			}//end foreach
			
			$user_id=PVDatabase::makeSafe($args['user_id']);
			$query="UPDATE ".PVDatabase::getUsersTableName()." SET $UPDATE_CLAUSE WHERE user_id='$user_id' ";
			
			PVDatabase::query($query);
			
		}//end if not empty
		
	}//end updateUserField
	
	/**
	 * Created a user roles. Role can later be assigned to the user.
	 * 
	 * @param array $args Arguements that make up a user role
	 * 			-'role_name' _string_: The name of the role
	 * 			-'role_type' _string_: The type of the role. Can be used to distinguish roles with the same name
	 * 			-'role_description' _string_: Text that descriptions the role
	 * 			-'is_editable' _boolean_: Determines if the role is editable
	 * 
	 * @return int role_id: The id of the newly created role.
	 * @access public
	 */
	public static function addUserRole($args=array()){
		$args += self::getUserRoleDefaults();
		$args=PVDatabase::makeSafe($args);
		
		if(is_array($args) && !empty($args)){
			
			extract($args);
			$is_editable=ceil($is_editable);
			
			$query="INSERT INTO ".PVDatabase::getUserRolesTableName()."( role_name, role_description, role_type, is_editable) VALUES('$role_name', '$role_description', '$role_type', '$is_editable')";
			$role_id=PVDatabase::return_last_insert_query($query, 'role_id', PVDatabase::getUserRolesTableName());
			
			return $role_id;
			
		}//end if
		
	}//end addUserRole
	
	/**
	 * Retrievles the user's role content. The arguements passed followed the ProdigyView Standard Search Query.
	 * ',' seperate objects to form an OR
	 * '+' seperate objects to form an AND
	 * 
	 * @param array $args An array of arguements to pass when removeing roles
	 * 		-'role_id' _id_: The role id
	 * 		@see PVUsers::addUserRole : Same arguements and be passed here when searching.
	 */
	public static function getUserRolesList($args=array()){
		$args += self::getUserRoleDefaults();
		$args += self::_getSqlSearchDefaults();
		
		$custom_where=$args['custom_where'];
		$custom_join=$args['custom_join'];
		$prefix_args=$args['prefix_args'];
		$PREQUERY=$args['prequery'];
		$args = PVDatabase::makeSafe($args);
		extract($args, EXTR_SKIP);
		
		$content_array=array();
		
		$first=1;
		$WHERE_CLAUSE='';
			
		if(!empty($role_id)){
					
			$role_id=trim($role_id);
				
				if($first==0 && ($role_id[0]!='+' && $role_id[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($role_id[0]=='+' || $role_id[0]==',') && $first==1 ){
					$role_id[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($role_id, 'role_id');
				
				$first=0;
		}//end not empty app_id
			
		if(!empty($role_name)){
					
				$role_name=trim($role_name);
				
				if($first==0 && ($role_name[0]!='+' && $role_name[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($role_name[0]=='+' || $role_name[0]==',') && $first==1 ){
					$role_name[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($role_name, 'role_name');
				
				$first=0;
			}//end not empty app_id
			
			
			if(!empty($role_type)){
					
				$role_type=trim($role_type);
				
				if($first==0 && ($role_type[0]!='+' && $role_type[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($role_type[0]=='+' || $role_type[0]==',') && $first==1 ){
					$role_type[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($role_type, 'role_type');
				
				$first=0;
			}//end not empty app_id
			
			
			if(!empty($is_editable)){
					
				$is_editable=trim($is_editable);
				
				if($first==0 && ($is_editable[0]!='+' && $is_editable[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($is_editable[0]=='+' || $is_editable[0]==',') && $first==1 ){
					$is_editable[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($is_editable, 'is_editable');
				
				$first=0;
			}//end not empty app_id
			
		if(!empty($WHERE_CLAUSE)){
			$WHERE_CLAUSE=' WHERE '.$WHERE_CLAUSE;
		}
		
		$ORDER_BY=$args['order_by'];
		
		$LIMIT=$args['limit'];
		
		if(!empty($LIMIT)){
			$LIMIT=" limit $LIMIT ";
		}
		
		if(!empty($ORDER_BY)){
			$ORDER_BY="ORDER BY $ORDER_BY ";
		}
	
		$query="SELECT * FROM ".PVDatabase::getUserRolesTableName()." ".$WHERE_CLAUSE." $ORDER_BY $LIMIT ";
		
		$result=PVDatabase::query($query);
	
		while ($row = PVDatabase::fetchArray($result)){
			array_push($content_array, $row);
    	}//end while
    	
    	$content_array=PVDatabase::formatData($content_array);
    	
		return $content_array;	
	}//end get user Role list
	
	/**
	 * Returns a user role based up on that role's id.
	 * 
	 * @param id $role_id The id of the role to be retrieved
	 * 
	 * @return array $role The role information in an array
	 */
	public static function getUserRoleByID($role_id){
		
		if(!empty($role_id)){
			
			$role_id=PVDatabase::makeSafe($role_id);
			
			$query="SELECT * FROM ".PVDatabase::getUserRolesTableName()." WHERE role_id='$role_id'";
			
			$result=PVDatabase::query($query);
			$row = PVDatabase::fetchArray($result);
			$row=PVDatabase::formatData($row);
			
			return $row;
			
		}//end page id
		
	}//getUserRoleByID
	
	/**
	 * Get user role by the name of the role. Beaware that using this method will return the first
	 * result of two or more roles of have the same name.
	 * 
	 * @param string $role_name The name of the role
	 * 
	 * @return array $role The data pertaining to a role
	 * @access public
	 */
	public static function getUserRoleByName($role_id){
		
		if(!empty($role_id)){
			
			$role_id=PVDatabase::makeSafe($role_id);
			
			$query="SELECT * FROM ".PVDatabase::getUserRolesTableName()." WHERE role_name='$role_id'";
			
			$result=PVDatabase::query($query);
			$row = PVDatabase::fetchArray($result);
			$row=PVDatabase::formatData($row);
			
			return $row;
			
		}//end page id
		
	}//end getUserRoleByName
	
	/**
	 * Update a user role based on the role id
	 * 
	 * @param arrays $role Data pertaining to a role.
	 * 			-'role_id' _id_: The id of the role. Is required
	 * 			-'role_name' _string_: The name of the role.
	 * 			-'role_type' _string_: The type of the role. Can be used to distinguish roles with the same name
	 * 			-'role_description' _string_: Text that descriptions the role
	 * 			-'is_editable' _boolean_: Determines if the role is editable
	 * 
	 * @return void
	 * @access public
	 */
	public static function updateUserRole($args=array()){
		$args += self::getUserRoleDefaults();
		$args=PVDatabase::makeSafe($args);
		
		if(is_array($args) && !empty($args['role_id']) ){
			$role_id=PVDatabase::makeSafe($role_id);
			$role_type=$role_type;
			$is_editable=ceil($is_editable);
			
			$query="UPDATE ".PVDatabase::getUserRolesTableName()." SET role_name='$role_name' , role_description='$role_description' , role_type='$role_type' , is_editable='$is_editable' WHERE role_id='$role_id'";
			PVDatabase::query($query);
			
		}//end if
		
	}//end updateUserRole
	
	/**
	 * Removes a role based on the role id and also removes any users from that role
	 * 
	 * @param id $role_id The id of the role to be deleted
	 * 
	 * @return void
	 * @access public
	 */
	public static function deleteUserRole($role_id){
		$role_id=PVDatabase::makeSafe($role_id);
		
		if(!empty($role_id)){
			$query="DELETE FROM ".PVDatabase::getUserRolesTableName()." WHERE role_id='$role_id' ";
			PVDatabase::query($query);
			
			$query="DELETE FROM ".PVDatabase::getUserRolesRelationsTableName()." WHERE role_id='$role_id' ";
			PVDatabase::query($query);
		}//end role_id
	}//end deleteUserRole
	
	
	public static function loginUser($username, $options=array() ){
		$defaults=array(
			'set_cookies'=>true,
		);
		
		$options += $defaults;
		
		$loginSuccess=0;
		$user_found=false;
		$username=PVDatabase::makeSafe($username);
		$password=PVDatabase::makeSafe($password);
		
		$query="SELECT ".PVDatabase::getUsersTableName().".user_id, username, user_email, user_password, ".PVDatabase::getUserRolesTableName().".role_id, role_type FROM ".PVDatabase::getUsersTableName()." 
		LEFT JOIN ".PVDatabase::getUserRolesRelationsTableName()." ON ".PVDatabase::getUsersTableName().".user_id=".PVDatabase::getUserRolesRelationsTableName().".user_id
		LEFT JOIN ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id=".PVDatabase::getUserRolesRelationsTableName().".role_id
		WHERE username='$username' ";
		
		$result =PVDatabase::query($query);
		
        if( PVDatabase::resultRowCount($result) > 0) {
        	$user_found=true;
			$loginSuccess=1;
		} else {
			$query="SELECT ".PVDatabase::getUsersTableName().".user_id, username, user_email, user_password, ".PVDatabase::getUserRolesTableName().".role_id, role_type FROM ".PVDatabase::getUsersTableName()." 
		LEFT JOIN ".PVDatabase::getUserRolesRelationsTableName()." ON ".PVDatabase::getUsersTableName().".user_id=".PVDatabase::getUserRolesRelationsTableName().".user_id
		LEFT JOIN ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id=".PVDatabase::getUserRolesRelationsTableName().".role_id
		WHERE user_email='$username' ";
		$result=PVDatabase::query($query);
			
			if( PVDatabase::resultRowCount($result) > 0) {
        		$user_found=true;
				$loginSuccess=1;
			}
			
		}//end else
		
		if($user_found){
			$row=PVDatabase::fetchArray($result);
			
			$user_id=$row['user_id'];
			$email=$row['user_email'];
			$username=$row['username'];
			$dbpassword=$row['user_password'];
			$role_id=$row['role_id'];
			$role_type=$row['role_type'];
			$user_access_level=$row['user_access_level'];
			$roles=self::getAssignedUserRoles($user_id);
			self::setUserSession($user_id, $username, $email , $roles, $user_access_level );
			
			if($options['set_cookie']){
				self::setUserCookies($user_id, $username, $email, $roles, $user_access_level );
			}
		}
		
		return $loginSuccess;
		
	}//end pvLogin
	
	
	
	private static function comparePasswords($password, $dbpassword, $password_encoded=false){
		if($password_encoded==false){
			$password=md5($password);
		}
		
		if($password==$dbpassword){
			return 1;	
		}
		else{
			return 0;	
		}
	}//end comparePassword
	
	private static function setUserSession($user_id, $username, $email , $roles, $access_level=0 ){
		if(empty($username)){
			$username=$email;	
		}
		
		PVSession::writeSession('pv_userid', $user_id);
		PVSession::writeSession('pv_username', $username);
		PVSession::writeSession('pv_useremail', $email);
		PVSession::writeSession('pv_roles', $roles);
		PVSession::writeSession('pv_access_level', $access_level);
			
	}
	
	private static function setUserCookies($user_id, $username, $email , $roles, $access_level=0  ){
		if(empty($username)){
			$username=$email;	
		}
		
		PVSession::writeCookie('pv_userid', $user_id);
		PVSession::writeCookie('pv_username', $username);
		PVSession::writeCookie('pv_useremail', $email);
		PVSession::writeCookie('pv_roles', $roles);
		PVSession::writeCookie('pv_access_level', $access_level);
		
	}
	
	public static function getOnlineUsersSession(){
		$MAX_IDLE_TIME=10;
		
		session_save_path("/path/to/custom/directory"); 
		if ( $directory_handle = opendir( session_save_path() ) ) { 
			$count = 0; 
			while ( false !== ( $file = readdir( $directory_handle ) ) ) { 
				if($file != '.' && $file != '..'){ 
					// Comment the 'if(...){' and '}' lines if you get a significant amount of traffic 
					if(time()- fileatime(session_save_path() . '\\' . $file) < $MAX_IDLE_TIME * 60) { 
						$count++; 
					}
				} 
			} 
			closedir($directory_handle); 

		return $count; 

		} 
		else { 
			return false; 
		} 
	
	
	}//end getOnlineUsersSession
	
	public static function logout(){
		
		if(isset($_SESSION['pv_userid'])){
			$session_exist=true;
		}

		$user_info=self::getUserInfo(self::getUserID());
		
		unset($_SESSION['pv_userid']);
		unset($_SESSION['pv_username']);
		unset($_SESSION['pv_useremail']);
		unset($_SESSION['pv_role_id']);
		unset($_SESSION['pv_role_type']);
		unset($_SESSION['pv_access_level']);
		
		setcookie("pv_userid", $user_info['user_id'], time()-4800);
		setcookie("pv_username", $user_info['username'] , time()-4800);
		setcookie("pv_useremail", $user_info['user_email'], time()-4800);
		setcookie("pv_role_id", $role_id, time()-4800);
		setcookie("pv_role_type", $role_type, time()-4800);
		setcookie("pv_access_level", $pv_access_level, time()-4800);
		
		if($session_exist){
			session_destroy();
		}
		
		

	}//end logout
	
	public static function getUserList($args=array()){
		$args += self::getUserDefaults();
		$args += self::_getSqlSearchDefaults();
		
		$custom_where=$args['custom_where'];
		$custom_join=$args['custom_join'];
		$prefix_args=$args['prefix_args'];
		$PREQUERY=$args['prequery'];
		$args = PVDatabase::makeSafe($args);
		extract($args, EXTR_SKIP);
	
		$user_array=array();
		$table_name=PVDatabase::getUsersTableName();
		$db_type=PVDatabase::getDatabaseType();
		
			$first=1;
			
			$content_array=array();
				
			$WHERE_CLAUSE='';
			
			if(!empty($is_active)){
					
				$is_active=trim($is_active);
				
				if($first==0 && ($is_active[0]!='+' && $is_active[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($is_active[0]=='+' || $is_active[0]==',') && $first==1 ){
					$is_active[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($is_active, 'is_active');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($user_email)){
					
				$user_email=trim($user_email);
				
				if($first==0 && ($user_email[0]!='+' && $user_email[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($user_email[0]=='+' || $user_email[0]==',') && $first==1 ){
					$user_email[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_email, 'user_email');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($user_password)){
					
				$user_password=trim($user_password);
				
				if($first==0 && ($user_password[0]!='+' && $user_password[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($user_password[0]=='+' || $user_password[0]==',') && $first==1 ){
					$user_password[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_password, 'user_password');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($username)){
					
				$username=trim($username);
				
				if($first==0 && ($username[0]!='+' && $username[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($username[0]=='+' || $username[0]==',') && $first==1 ){
					$username[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($username, 'username');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($receive_html_emails)){
					
				$receive_html_emails=trim($receive_html_emails);
				
				if($first==0 && ($receive_html_emails[0]!='+' && $receive_html_emails[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($receive_html_emails[0]=='+' || $receive_html_emails[0]==',') && $first==1 ){
					$receive_html_emails[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($receive_html_emails, 'receive_html_emails');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($user_id)){
					
				$user_id=trim($user_id);
				
				if($first==0 && ($user_id[0]!='+' && $user_id[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($user_id[0]=='+' || $user_id[0]==',') && $first==1 ){
					$user_id[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_id, PVDatabase::getUsersTableName().'.user_id');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($registration_date) ){
					
				$registration_date=trim($registration_date);
				
				if($first==0 && ($registration_date[0]!='+' && $registration_date[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($registration_date[0]=='+' || $registration_date[0]==',') && $first==1 ){
					$user_id[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($registration_date, 'registration_date');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($activation_date) ){
					
				$activation_date=trim($activation_date);
				
				if($first==0 && ($activation_date[0]!='+' && $activation_date[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($activation_date[0]=='+' || $activation_date[0]==',') && $first==1 ){
					$activation_date[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($activation_date, 'activation_date');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($user_image) ){
					
				$user_image=trim($user_image);
				
				if($first==0 && ($user_image[0]!='+' && $user_image[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($user_image[0]=='+' || $user_image[0]==',') && $first==1 ){
					$user_image[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_image, 'user_image');
				
				$first=0;
			}//end not empty app_id
			
			if(!empty($user_image_thumb) ){
					
				$user_image_thumb=trim($user_image_thumb);
				
				if($first==0 && ($user_image_thumb[0]!='+' && $user_image_thumb[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($user_image_thumb[0]=='+' || $user_image_thumb[0]==',') && $first==1 ){
					$user_image_thumb[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_image_thumb, 'user_image_thumb');
				
				$first=0;
			}//end not empty app_id
			
		$JOINS='';
		
		if(!empty($WHERE_CLAUSE)){
			$WHERE_CLAUSE=' WHERE '.$WHERE_CLAUSE;
		}
		
    	if(!empty($custom_where)){
			
			if(empty($WHERE_CLAUSE)){
				$WHERE_CLAUSE.=' WHERE ';
			}
			
			$WHERE_CLAUSE.=" $custom_where";
		}
		
		if(!empty($custom_join)){
			$JOINS.=" $custom_join ";
		}
		
		if($join_user_roles==1){
			$JOINS.=" JOIN ".PVDatabase::getUserRolesRelationsTableName()." ON ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id
		JOIN ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id=".PVDatabase::getUserRolesRelationsTableName().".role_id ";
		}
		
		if(!empty($distinct)){
			$prefix_args.=" DISTINCT $distinct, ";
		}
		
		if(!empty($limit) && $db_type=='mssql' && !$paged){
			$prefix_args.=" TOP $limit ";
		}
		
		if($paged){
			$page_results=PVDatabase::getPagininationOffset($table_name, $JOINS , $WHERE_CLAUSE, $current_page, $results_per_page, $order_by);
			
			if($db_type=='mysql' || $db_type=='postgresql'){
				$limit=' '.$page_results['limit_offset'];
			}
			else if($db_type=='mssql'){
				$WHERE_CLAUSE.=' '.$page_results['limit_offset'];
				$table_name=$page_results['from_clause'];
			}
		}
		
		if(!empty($group_by)){
			$WHERE_CLAUSE.=" GROUP BY $group_by";
		}
		
		if(!empty($having)){
			$WHERE_CLAUSE.=" HAVING $having";
		}
		
		if(!empty($order_by)){
			$WHERE_CLAUSE.=" ORDER BY $order_by";
		}
		
		if(!empty($limit) && !$paged && ($db_type=='mysql' || $db_type=='postgresql') ){
			$WHERE_CLAUSE.=" LIMIT $limit";
		}
		
		if($paged){
			$WHERE_CLAUSE.=" $limit";
		}
		
		if(empty($custom_select)){
			$custom_select='*';
		}
		
    	$query="$PREQUERY SELECT $prefix_args $custom_select FROM $table_name $JOINS $WHERE_CLAUSE";
    	
		$result = PVDatabase::query($query);
    	
    	while ($row = PVDatabase::fetchArray($result)){
			if($paged){
				$row['current_page']=$page_results['current_page'];
				$row['last_page']=$page_results['last_page'];
				$row['total_pages']=$page_results['total_pages'];
			}
			
    		array_push($content_array, $row);
    	}//end while
    	
    	$content_array=PVDatabase::formatData($content_array);
		
    	return $content_array;
	}//getUserList
	
	public static function getUserListWithRoles($args=''){
		
		$user_array=array();
		
		if(is_array($args)){
			$args=PVDatabase::makeSafe($args);
			extract($args);
		
		
			$first=1;
			
			$content_array=array();
				
			$WHERE_CLAUSE.='';
			
			if(!empty($is_active)){
					
				$is_active=trim($is_active);
				
				if($first==0 && ($is_active[0]!='+' && $is_active[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($is_active[0]=='+' || $is_active[0]==',') && $first==1 ){
					$is_active[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($is_active, 'is_active');
				
				$first=0;
			}//end not empty app_id
			
			
			if(!empty($username)){
					
				$username=trim($username);
				
				if($first==0 && ($username[0]!='+' && $username[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($username[0]=='+' || $username[0]==',') && $first==1 ){
					$username[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($username, 'username');
				
				$first=0;
			}//end not empty app_id
			
			
			
			if(!empty($receive_html_emails)){
					
				$receive_html_emails=trim($receive_html_emails);
				
				if($first==0 && ($receive_html_emails[0]!='+' && $receive_html_emails[0]!=',' ) ){
						$receive_html_emails.=" AND ";
					}
					
				else if( ($receive_html_emails[0]=='+' || $receive_html_emails[0]==',') && $first==1 ){
					$receive_html_emails[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($receive_html_emails, 'receive_html_emails');
				
				$first=0;
			}//end not empty app_id
			
			
			if(!empty($user_id)){
					
				$user_id=trim($user_id);
				
				if($first==0 && ($user_id[0]!='+' && $user_id[0]!=',' ) ){
						$user_id.=" AND ";
					}
					
				else if( ($user_id[0]=='+' || $user_id[0]==',') && $first==1 ){
					$user_id[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($user_id, 'user_id');
				
				$first=0;
			}//end not empty app_id
			
			
			if(!empty($role_id)){
					
				$role_id=trim($role_id);
				
				if($first==0 && ($role_id[0]!='+' && $role_id[0]!=',' ) ){
						$WHERE_CLAUSE.=" AND ";
					}
					
				else if( ($role_id[0]=='+' || $role_id[0]==',') && $first==1 ){
					$role_id[0]='';
				}
				
				$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($role_id, 'pv_user_roles.role_id');
				
				$first=0;
			}//end not empty app_id
			
		}//end if is_array
		
			if(!empty($WHERE_CLAUSE)){
				$WHERE_CLAUSE=' WHERE '.$WHERE_CLAUSE;
			}
			
			$ORDER_BY=$args['order_by'];
			
			$LIMIT=$args['limit'];
			
		
			
			if(!empty($LIMIT)){
				$LIMIT=" limit $LIMIT ";
			}
			
			if(!empty($ORDER_BY)){
				$ORDER_BY="ORDER BY $ORDER_BY ";
			}
		
		$query="SELECT * FROM ".$schema."pv_login 
		JOIN ".$schema."pv_user_roles_relations ON pv_user_roles_relations.user_id=pv_login.user_id
		JOIN ".$schema."pv_user_roles ON pv_user_roles.role_id=pv_user_roles_relations.role_id
		".$WHERE_CLAUSE." $ORDER_BY $LIMIT ";
		
		$result=PVDatabase::query($query);
		
		while($row = PVDatabase::fetchArray($result)){
			$user_array[$row['user_id']]=$row;
		}
	
		return $user_array;
	}//getUserList
	
	public static function getUserInfo($userid, $join_activation=FALSE , $join_roles=FALSE){
	
		$JOINS='';
		
		if($join_roles==true){
			$JOINS.=" JOIN  ".PVDatabase::getUserRolesRelationsTableName()." ON ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id
			JOIN  ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id=".PVDatabase::getUserRolesRelationsTableName().".role_id ";
					
		}
		
		if($join_activation==true){
			$JOINS.=" JOIN  ".PVDatabase::getUserActivationTableName()." ON ".PVDatabase::getUserActivationTableName().".user_id=".PVDatabase::getUsersTableName().".user_id";
		}
		
		
		if($userid!=0 && PVValidator::isID($userid) ){
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." $JOINS WHERE ".PVDatabase::getUsersTableName().".user_id='$userid' ";
			$result=PVDatabase::query($query);
			
			$row = PVDatabase::fetchArray($result);
   
    		return $row;
		}//end if
		else if(!empty($userid) && PVValidator::isValidEmail($userid) ){
	
			$userid=PVDatabase::makeSafe($userid);
			
			
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." $JOINS WHERE ".PVDatabase::getUsersTableName().".user_email='$userid' ";
			
			$result=PVDatabase::query($query);
			
			$row = PVDatabase::fetchArray($result);
   
    		return $row;									
													
		}
		
		else if(!empty($userid) ){
			
			$userid=PVDatabase::makeSafe($userid);
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()." $JOINS WHERE ".PVDatabase::getUsersTableName().".username='$userid' ";
			
			$result=PVDatabase::query($query);
			
			$row = PVDatabase::fetchArray($result);
   
    		return $row;									
													
		}
 	
	}//end getUserInfo
	
	
	public static function getUserInfoWithRoles($userid, $userfield=''){
			
		if($userid!=0 && PVValidator::isID($userid) ){
			
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()."
			JOIN  ".PVDatabase::getUserRolesRelationsTableName()." ON  ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id
			JOIN  ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id= ".PVDatabase::getUserRolesRelationsTableName().".role_id
			WHERE ".PVDatabase::getUsersTableName().".user_id='$userid' ";
			
		}//end if
		else if(!empty($userid) && PVValidator::isValidEmail($userid) ){
			
			$userid=PVDatabase::makeSafe($userid);
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()."
			JOIN  ".PVDatabase::getUserRolesRelationsTableName()." ON  ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id
			JOIN  ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id= ".PVDatabase::getUserRolesRelationsTableName().".role_id
			WHERE user_email='$userid' ";				
													
		}
		
		else if(!empty($userid) ){
			
			$userid=PVDatabase::makeSafe($userid);
			
			$query="SELECT * FROM ".PVDatabase::getUsersTableName()."
			JOIN  ".PVDatabase::getUserRolesRelationsTableName()." ON  ".PVDatabase::getUserRolesRelationsTableName().".user_id=".PVDatabase::getUsersTableName().".user_id
			JOIN  ".PVDatabase::getUserRolesTableName()." ON ".PVDatabase::getUserRolesTableName().".role_id= ".PVDatabase::getUserRolesRelationsTableName().".role_id
			WHERE username='$userid' ";			
		}
		
		if(!empty($query)){
			$result=PVDatabase::query($query);
			
			$row = PVDatabase::fetchArray($result);
			$row=PVDatabase::formatData($row);
   
    		return $row;
		}
 	
	}//end getUserInfo
	
	public static function getUserIDByName($username){
			
			$username=PVDatabase::makeSafe($username);
			$query="SELECT user_id FROM ".PVDatabase::getUsersTableName()." WHERE username='$username' ";
			
			$result=PVDatabase::query($query);
			if(PVDatabase::resultRowCount($result) >0){
				$row = PVDatabase::fetchArray($result);
				return $row['user_id'];
			}
			else{
				return 0;
			}
	}//end getUserIDByName
	
	public static function getUserIDByEmail($useremail){
		
		$useremail=PVDatabase::makeSafe($useremail);	
		$query="SELECT user_id FROM ".PVDatabase::getUsersTableName()." WHERE user_email='$useremail' ";
		$result=PVDatabase::query($query);
			
		if(PVDatabase::resultRowCount($result) >0){
			$row = PVDatabase::fetchArray($result);
			return $row['user_id'];
		}
		else{
			return 0;
		}
	}//end getUserIDByName
	
	public static function addUserRelationship($requesting_user_id, $requested_user_id, $relationship_type='', $relationship_status=0 ){
		
		if(is_array($requesting_user_id)){
	
			$requested_user_id=$requesting_user_id['requested_user_id'];
			$relationship_type=$requesting_user_id['relationship_type'];
			$relationship_status=$requesting_user_id['relationship_status'];
			$requesting_user_id=$requesting_user_id['requesting_user_id'];
		}
		
		$requesting_user_id=PVDatabase::makeSafe($requesting_user_id);
		$requested_user_id=PVDatabase::makeSafe($requested_user_id);
		$relationship_status=ceil($relationship_status);
		$relationship_type=PVDatabase::makeSafe($relationship_type);
		
		$query="INSERT INTO ".PVDatabase::getUserRelationsTableName()."( requesting_user, requested_user, relationship_status, relationship_type) VALUES('$requesting_user_id', '$requested_user_id', '$relationship_status' , '$relationship_type' )";
		$relationship_id=PVDatabase::return_last_insert_query($query, 'relationship_id', PVDatabase::getUserRelationsTableName());
		
		return $relationship_id;
	}//end addUserRelationship
	
	
	public static function addUniqueUserRelationship($requesting_user_id, $requested_user_id, $relationship_type='', $relationship_status=0 ){
		
		if(is_array($requesting_user_id)){
	
			$requested_user_id=$requesting_user_id['requested_user_id'];
			$relationship_type=$requesting_user_id['relationship_type'];
			$relationship_status=$requesting_user_id['relationship_status'];
			$requesting_user_id=$requesting_user_id['requesting_user_id'];
			
		}
		
		$requesting_user_id=PVDatabase::makeSafe($requesting_user_id);
		$requested_user_id=PVDatabase::makeSafe($requested_user_id);
		$relationship_status=ceil($relationship_status);
		$relationship_type=PVDatabase::makeSafe($relationship_type);
		
		$query="SELECT relationship_id FROM ".PVDatabase::getUserRelationsTableName()." WHERE requesting_user='$requesting_user_id' AND requested_user='$requested_user_id' ";
		
		if(!empty($relationship_type)){
			$query.=" AND relationship_type='$relationship_type' ";
		}
		
		$result=PVDatabase::query($query);
		
		if(PVDatabase::resultRowCount($result)<=0 ){
		
			$query="INSERT INTO ".PVDatabase::getUserRelationsTableName()."( requesting_user, requested_user, relationship_status, relationship_type) VALUES('$requesting_user_id', '$requested_user_id', '$relationship_status' , '$relationship_type' )";
			
			$relationship_id=PVDatabase::return_last_insert_query($query, 'relationship_id', PVDatabase::getUserRelationsTableName());
		
		}
		
		return $relationship_id;
	}//end addUserRelationship
	
	public static function getUserRelationshipList($requesting_user_id='', $requested_user_id='', $relationship_type='', $relationship_status=0){
		
		
		
		if(is_array($requesting_user_id)){
	
			$contents=$requesting_user_id;
			$requesting_user_id='';
			extract($contents);
		}
		
		$first=1;
		
		$content_array=array();
		
		$table_name=PVDatabase::getUserRelationsTableName();
		$db_type=PVDatabase::getDatabaseType();
			
		$WHERE_CLAUSE.='';
			
			
			
		if(!empty($requesting_user)){
				
			$requesting_user=trim($requesting_user);
			
			if($first==0 && ($requesting_user[0]!='+' && $requesting_user[0]!=',' ) ){
					$WHERE_CLAUSE.=" AND ";
				}
				
			else if( ($requesting_user[0]=='+' || $requesting_user[0]==',') && $first==1 ){
				$requesting_user[0]='';
			}
			
			$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($requesting_user, 'requesting_user');
			
			$first=0;
		}//end not empty app_id
		
		
		
		if(!empty($requested_user)){
				
			$requested_user=trim($requested_user);
			
			if($first==0 && ($requested_user[0]!='+' && $requested_user[0]!=',' ) ){
					$WHERE_CLAUSE.=" AND ";
				}
				
			else if( ($requested_user[0]=='+' || $requested_user[0]==',') && $first==1 ){
				$requesting_user[0]='';
			}
			
			$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($requested_user, 'requested_user');
			
			$first=0;
		}//end not empty app_id
		
		
		
		if(!empty($relationship_type)){
				
			$relationship_type=trim($relationship_type);
			
			if($first==0 && ($relationship_type[0]!='+' && $relationship_type[0]!=',' ) ){
					$WHERE_CLAUSE.=" AND ";
				}
				
			else if( ($relationship_type[0]=='+' || $relationship_type[0]==',') && $first==1 ){
				$relationship_type[0]='';
			}
			
			$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($relationship_type, 'relationship_type');
			
			$first=0;
		}//end not empty app_id
		
		
		
		if(!empty($relationship_status)){
				
			$relationship_status=trim($relationship_status);
			
			if($first==0 && ($relationship_status[0]!='+' && $relationship_status[0]!=',' ) ){
					$WHERE_CLAUSE.=" AND ";
				}
				
			else if( ($relationship_status[0]=='+' || $relationship_status[0]==',') && $first==1 ){
				$relationship_status[0]='';
			}
			
			$WHERE_CLAUSE.=' '.PVTools::parseSQLOperators($relationship_status, 'relationship_status');
			
			$first=0;
		}//end not empty app_id
		
		$JOINS='';
		
		if(!empty($custom_where)){
			$WHERE_CLAUSE.=" $custom_where";
		}
		
		if(!empty($custom_join)){
			$JOINS.=" $custom_join ";
		}
		
		
		
		
		if(!empty($WHERE_CLAUSE)){
			$WHERE_CLAUSE=' WHERE '.$WHERE_CLAUSE;
		}
	
		if(!empty($distinct)){
			$prefix_args.=" DISTINCT $distinct, ";
		}
		
		if(!empty($limit) && $db_type=='mssql' && !$paged){
			$prefix_args.=" TOP $limit ";
		}
		
		if($paged){
			$page_results=PVDatabase::getPagininationOffset($table_name, $JOINS , $WHERE_CLAUSE, $current_page, $results_per_page, $order_by);
			
			if($db_type=='mysql' || $db_type=='postgresql'){
				$limit=' '.$page_results['limit_offset'];
			}
			else if($db_type=='mssql'){
				$WHERE_CLAUSE.=' '.$page_results['limit_offset'];
				$table_name=$page_results['from_clause'];
			}
		}
		
	
		if(!empty($group_by)){
			$WHERE_CLAUSE.=" GROUP BY $group_by";
		}
		
		if(!empty($having)){
			$WHERE_CLAUSE.=" HAVING $having";
		}
		
		if(!empty($order_by)){
			$WHERE_CLAUSE.=" ORDER BY $order_by";
		}
		
		if(!empty($limit) && !$paged && ($db_type=='mysql' || $db_type=='postgresql') ){
			$WHERE_CLAUSE.=" LIMIT $limit";
		}
		
		if($paged){
			$WHERE_CLAUSE.=" $limit";
		}
		
		if(empty($custom_select)){
			$custom_select='*';
		}
    	$query="$prequery SELECT $prefix_args $custom_select FROM $table_name $JOINS $WHERE_CLAUSE";
    	
		$result = PVDatabase::query($query);
    	
    	while ($row = PVDatabase::fetchArray($result)){
			if($paged){
				$row['current_page']=$page_results['current_page'];
				$row['last_page']=$page_results['last_page'];
				$row['total_pages']=$page_results['total_pages'];
			}
			
    		array_push($content_array, $row);
    	}//end while
    	
    	$content_array=PVDatabase::formatData($content_array);
		
    	return $content_array;

		
	}//end getUserRelationshipList
	
	
	public static function updateUserRelationship($relationship_id, $requesting_user_id=0, $requested_user_id=0, $relationship_type='', $relationship_status=0 ){
		
		if(is_array($relationship_id)){
			$requesting_user=$relationship_id['requesting_user'];
			$requested_user=$relationship_id['requested_user'];
			$relationship_type=$relationship_id['relationship_type'];
			$relationship_status=$relationship_id['relationship_status'];
			$relationship_id=$relationship_id['relationship_id'];
			
			if(empty($requesting_user)){
				$requesting_user=$relationship_id['requesting_user'];
			}
			
			if(empty($requested_user)){
				$requested_user=$relationship_id['requested_user'];
			}
			
		}
		
		$requesting_user_id=PVDatabase::makeSafe($requesting_user_id);
		$requested_user_id=PVDatabase::makeSafe($requested_user_id);
		$relationship_status=ceil($relationship_status);
		$relationship_type=PVDatabase::makeSafe($relationship_type);
		
		$query="UPDATE ".PVDatabase::getUserRelationsTableName()." SET requesting_user='$requesting_user_id',  requested_user='$requested_user_id', relationship_type='$relationship_type', relationship_status='$relationship_status' WHERE  relationship_id='$relationship_id' ";
		
		PVDatabase::query($query);
		
	}//end upateUserRelationship
	
	public static function getUserRelationship($relationship_id){
		
		if(!empty($relationship_id)){
			
			$relationship_id=PVDatabase::makeSafe($relationship_id);
			
			$query="SELECT * FROM ".PVDatabase::getUserRelationsTableName()." WHERE relationship_id='$relationship_id' ";
			
			$result=PVDatabase::query($query);
			$row = PVDatabase::fetchArray($result);
			
			$row=PVDatabase::formatData($row);
			
			return $row;
			
		}//end if not empty
		
		
	}//end getUserRelationship
	
	public static function getUserRelationshipByConnection($first_user, $second_user, $relationship_type='', $relationship_status=''){
		
		if(!empty($first_user) && !empty($second_user)){
			
			$relationship_id=PVDatabase::makeSafe($relationship_id);
			
			//Unused Possible Future Solution
			//$query="SELECT a.* FROM USER_RELATIONSHIP a WHERE a.requesting_user_id = '1' AND a.requested_user_id = '2' UNION SELECT b.* FROM USER_RELATIONSHIP b WHERE b.requesting_user_id = '2'  AND b.requested_user_id = '1'";		
			
			$query="SELECT * FROM ".PVDatabase::getUserRelationsTableName()." WHERE requesting_user IN($first_user, $second_user) AND requested_user IN($first_user, $second_user); ";
			$result=PVDatabase::query($query);
			$row = PVDatabase::fetchArray($result);
			
			$row=PVDatabase::formatData($row);
			
			return $row;
		}
		
		return 0;
		
	}//end checkUserRelationshipStatus
	
	public static function checkUserRelationship($first_user, $second_user, $relationship_type='', $relationship_status=''){
		
		if(!empty($first_user) && !empty($second_user)){
			
			$relationship_id=PVDatabase::makeSafe($relationship_id);
			
			//Unused Possible Future Solution
			//$query="SELECT a.* FROM USER_RELATIONSHIP a WHERE a.requesting_user_id = '1' AND a.requested_user_id = '2' UNION SELECT b.* FROM USER_RELATIONSHIP b WHERE b.requesting_user_id = '2'  AND b.requested_user_id = '1'";		
			
			$query="SELECT * FROM ".PVDatabase::getUserRelationsTableName()." WHERE requesting_user IN($first_user, $second_user) AND requested_user IN($first_user, $second_user) ";
			
			if(!empty($relationship_type)){
				$relationship_type=PVDatabase::makeSafe($relationship_type);
				$query.=" AND relationship_type='$relationship_type' ";
			}
			
			if(!empty($relationship_status)){
				$relationship_status=PVDatabase::makeSafe($relationship_status);
				$query.=" AND relationship_type='$relationship_status' ";
			}
			
			$result=PVDatabase::query($query);
			$row = PVDatabase::fetchArray($result);
			
			if(empty($row)){
				return 0;	
			}
			else{
				return 1;	
			}
			
			
		}
		
		return 0;
		
	}//end checkUserRelationshipStatus
	
	
	
	public static function deleteUserRelationship($relationship_id){
		
		if(!empty($relationship_id)){
			
			$relationship_id=PVDatabase::makeSafe($relationship_id);
			
			$query="DELETE  FROM ".PVDatabase::getUserRelationsTableName()." WHERE relationship_id='$relationship_id' ";
			
			PVDatabase::query($query);
			
		}//end if not empty
		
	}//end deleteUserRelationship
	
	public static function checkUserRole($user_id, $roles){
		
		if(PVValidator::isID($user_id) && !empty($user_id)){
			$user_list=self::getUserList(array('user_id'=>$user_id, 'join_user_roles'=>true));
		}
		else if(PVValidator::isValidEmail($user_id) && !empty($user_id) ){
			$user_list=self::getUserList(array('user_email'=>$user_id, 'join_user_roles'=>true));
		}
		else if(!empty($user_id)){
			$user_list=self::getUserList(array('username'=>$user_id, 'join_user_roles'=>true));
		}
		
		$roles_array=array();
		
		if(is_array($roles)){
			
			foreach($roles as $key=>$value){
				
				if(PVValidator::isID($value)){
					array_push($roles_array, $value );
				} else {
					$role=self::getUserRoleByName($value);
					if(!empty($role)){
						array_push($roles_array, $role['role_id'] );
					}
				}
			}//end foreach
		} else {
			if(PVValidator::isID($value)){
					array_push($roles_array, $value );
			} else {
				$role=self::getUserRoleByName($value);
				if(!empty($role)){
					array_push($roles_array, $role['role_id'] );
				}
			}
			
		}//end else
		
		if(!empty($roles_array) && !empty($user_list)){
			
			foreach($user_list as $key=>$value){
				
				if(in_array($value['role_id'], $roles_array)){
					return 1;	
				}
			}//end foreach
			
			return 0;
		}
		else{
			return 0;	
		}
		
	}//end checkUserRole
	
	private static function getUserDefaults() {
		$defaults = array(
			'user_email'=>'',
			'user_password'=>'',
			'is_active'=>0,
			'username'=>'',
			'receive_html_emails'=>0,
			'user_id'=>0,
			'user_access_level'=>'',
			'registration_date'=>'',
			'activation_date'=>'',
			'user_image'=>'',
			'user_image_thumb'=>'',
			'user_role'=>0,
			'activation_code'=>''
		);
		return $defaults;
	}
	
	private static function getUserRoleDefaults() {
		$defaults=array(
			'role_id'=>0,
			'role_name'=>'',
			'role_description'=>'',
			'role_type'=>'',
			'is_editable'=>0
		);
		
		return $defaults;
	}
	
}//end class 
	