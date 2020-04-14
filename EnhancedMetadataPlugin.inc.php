<?php
import('lib.pkp.classes.plugins.GenericPlugin');


class EnhancedMetadataPlugin extends GenericPlugin
{
	/**
	 * @return string plugin name
	 */
	public function getDisplayName()
	{
		return __('plugins.generic.enhancedMetadata.title');
	}

	/**
	 * @return string plugin description
	 */
	public function getDescription()
	{
		return __('plugins.generic.enhancedMetadata.desc');
	}


	public function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			HookRegistry::register('submissionsubmitstep3form::display', array($this, 'handleSubmissionDisplay'));
			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'handleSubmissionExecute'));
			HookRegistry::register('submissiondao::getLocaleFieldNames', array($this, 'addLocaleFieldNames'));
		}
		return $success;
	}

	function handleSubmissionDisplay($hookName, $args)
	{
		$request = PKPApplication::getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->registerFilter("output", array($this, 'submissionFilter'));
	}

	function handleSubmissionExecute($hookName, $params)
	{
		$form = $params[0];
		$form->readUserVars(array('enhTest', 'submissionId'));
		$submissionId = $form->getData('submissionId');
		$enhTest = $form->getData('enhTest');
		$submissionDao = Application::getSubmissionDAO();
		$submission = $submissionDao->getById($submissionId);
		$submission->setData('enhTest', $enhTest);
		$submissionDao->updateObject($submission);
	}

	function submissionFilter($output, $templateMgr)
	{
		if (preg_match('/<fieldset\s*id="tagitFields"\s*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset);
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('submissionMetaData.tpl'));
			$newOutput .= substr($output, $offset);
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'submissionFilter'));
		}
		return $output;
	}

	function addLocaleFieldNames($hookName, $params) {
		error_log("######");
		$fields =& $params[1];
		$fields[] = 'enhTest';
		return false;
	}


}
