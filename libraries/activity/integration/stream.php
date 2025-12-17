<?php

class activityintegrationstream
{ 	
/*Function to push like,Data And comment to Activity Stream
 * 
 * 
 * 
 * 
 */

	function pushActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access,$integration_option)
	{ 
		$activityintegrationstream= new activityintegrationstream();
		if($integration_option=='joomla')
		{
			return true;
		}
		else if($integration_option=='Community Builder')
		{
			$result=$activityintegrationstream->pushToCBActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access);

			if(!$result){
			}
		}
		else if($integration_option=='JomSocial' )
		{
			$result=$activityintegrationstream->pushToJomsocialActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access);
			if(!$result){
			}
		}
		else if($integration_option=='Jomwall')
		{
			$result=$activityintegrationstream->pushToJomwallActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access);
			if(!$result){
				return false;
			}
		}
		else if($integration_option=='EasySocial')
		{
			$result=$activityintegrationstream->pushToEasySocialActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access);
			if(!$result){
				return false;
			}
		}
		
		
		return true;
	}
	
	function pushToCBActivity($actor_id,$act_type,$act_subtype='',$act_description='',$act_link='',$act_title='',$act_access)
	{		
		//load CB framework
		global $_CB_framework, $mainframe;
		if(defined( 'JPATH_ADMINISTRATOR'))
		{
			if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';
				return false;
			}
			include_once( JPATH_ADMINISTRATOR.'/components/com_comprofiler/plugin.foundation.php' );
		}
		else
		{
			if(!file_exists($mainframe->getCfg('absolute_path').'/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';
				return false;
			}
			include_once( $mainframe->getCfg('absolute_path').'/administrator/components/com_comprofiler/plugin.foundation.php' );
		}
		
		cbimport('cb.plugins');
		cbimport('cb.html');
		cbimport('cb.database');
		cbimport('language.front');
		cbimport('cb.snoopy');
		cbimport('cb.imgtoolbox');
		
		global $_CB_framework, $_CB_database, $ueConfig;

		//load cb activity plugin class
		if(!file_exists(JPATH_SITE.DS."components".DS."com_comprofiler".DS."plugin".DS."user".DS."plug_cbactivity".DS."cbactivity.class.php"))
		{
			//echo 'CB Activity plugin not installed!';
			return false;
		}
		require_once(JPATH_SITE.DS."components".DS."com_comprofiler".DS."plugin".DS."user".DS."plug_cbactivity".DS."cbactivity.class.php");

		//push activity
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';
		
		$activity=new cbactivityActivity( $_CB_database );
		$activity->set('user_id',$actor_id);
		$activity->set('type',$act_type);
		$activity->set('subtype',$act_subtype);
		$activity->set('title', $act_description.' '.$linkHTML);
		$activity->set('icon','nameplate');
		$activity->set('date',cbactivityClass::getUTCDate() );
		$activity->store();
				
		return true;
	}
	
	function pushToJomsocialActivity($actor_id,$act_type='',$act_subtype='',$act_description='',$act_link='',$act_title='',$act_access)
	{		
		/*load Jomsocial core*/
		$linkHTML='';
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(file_exists($jspath)){
			include_once($jspath.DS.'libraries'.DS.'core.php');
		}
		
		//push activity
		if($act_title and $act_link)
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';		
		$act=new stdClass();
		$act->cmd='wall.write';
		$act->actor=$actor_id;
		$act->target=0; // no target
		$act->title='{actor} ' .$act_description.' '.$linkHTML;
		$act->content='';
		$act->app='wall';
		$act->cid=0;
		$act->access=$act_access;
		CFactory::load('libraries','activities');
		if (defined('CActivities::COMMENT_SELF')) {
			$act->comment_id = CActivities::COMMENT_SELF;
			$act->comment_type = 'profile.location';
		}
		if (defined('CActivities::LIKE_SELF')) {
				$act->like_id = CActivities::LIKE_SELF;
				$act->like_type = 'profile.location';
		}

		$res=CActivityStream::add($act);
		return true;
	}
	
	function pushToJomwallActivity($actor_id,$act_type,$act_subtype='',$act_description='',$act_link='',$act_title='',$act_access)
	{	
		/*load jomwall core*/
		if(!class_exists('AwdwallHelperUser')){
			require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');	
		}
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';
		$comment=$act_description.' '.$linkHTML;
		$attachment=$act_link;		
		$type='text';
		$imgpath=NULL;
		$params=array();
		
		AwdwallHelperUser::addtostream($comment,$attachment,$type,$actor_id,$imgpath,$params);
		
		return true;
	}
	
	
	function pushToEasySocialActivity($actor_id,$act_type,$act_subtype='',$act_description='',$act_link='',$act_title='',$act_access)
	{
		require_once( JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php' );
		if($actor_id!=0)
		$myUser = Foundry::user( $actor_id );
		$stream = Foundry::stream();
		$template = $stream->getTemplate();
		$template->setActor( $actor_id, SOCIAL_TYPE_USER );
		$template->setContext( $actor_id, SOCIAL_TYPE_USERS );
		$template->setVerb( 'invite' );
		$template->setType( SOCIAL_STREAM_DISPLAY_MINI );
		if($actor_id!=0)
		{
			$userProfileLink = '<a href="'. $myUser->getPermalink() .'">' . $myUser->getName() . '</a>';
			$title 	 = ($userProfileLink." ".$act_description);
		}
		else
		$title 	 = ("A guest ".$act_description);
		$template->setTitle( $title );
		$template->setAggregate( false );

		$template->setPublicStream( 'core.view' );
		$stream->add( $template );
		return true;
	}
	

}//class

