<?php
import('lib.pkp.classes.plugins.GenericPlugin');


class EnhancedMetadataPlugin extends GenericPlugin
{

	/* unused at the moment */
	var $json = [
		"name" => "enhTest",
		"type" => "text",
		"title" => ["de_DE" => "test_de", "en_EN" => "test_en"],
		"description" => ["de_DE" => "test_desc_de", "en_EN" => "test_desc_en"],
	];

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
			/* Expand locale fields of submission */
			/* TODO Hook doesn't work */
			HookRegistry::register('submissiondao::getLocaleFieldNames', array($this, 'addLocaleFieldNames'));

			/* Add metadata fields to submission */
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'handleSubmissionMetadataFormDisplay'));

			/* Save submission metadata fields into db  */
			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'handleSubmissionMetadataFormExecute'));

			/* Add metadata fields to supplementary files */
			HookRegistry::register('supplementaryfilemetadataform::display', array($this, 'handleSupplementaryMetadataFormDisplay'));

			/* Save supplementary  metadata fields into db */
			/* TODO Hook doesn't work */
			HookRegistry::register('supplementaryfilemetadataform::execute', array($this, 'handleSupplementaryMetadataFormExecute'));
			/*HookRegistry::register('submissionfilesmetadataform::execute', array($this, 'handleSupplementaryMetadataFormExecute'));*/
		}
		return $success;
	}

	function addLocaleFieldNames($hookName, $params)
	{
		error_log("######");
		$fields =& $params[1];
		$fields[] = 'enhTest';
		return false;
	}


	function handleSubmissionMetadataFormDisplay($hookName, $params)
	{
		$smarty =& $params[1];
		$output =& $params[2];
		$request = Application::getRequest();
		$contextId = $request->getContext()->getId();
		$output .= $smarty->fetch($this->getTemplateResource('submissionMetaData.tpl'));
	}

	function handleSubmissionMetadataFormExecute($hookName, $params)
	{
		$form = $params[0];
		$form->readUserVars(array('enhTest', 'submissionId'));
		$submissionId = $form->getData('submissionId');
		$enhTest = $form->getData('enhTest');
		$submissionDao = Application::getSubmissionDAO();
		$submission = $submissionDao->getById($submissionId);
		$submission->setData('enhTest', $enhTest, null); // localized
		$submissionDao->updateObject($submission);
	}

	function handleSupplementaryMetadataFormDisplay($hookName, $params)
	{
		error_log("+++++++");
		$request = PKPApplication::getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->registerFilter("output", array($this, 'supplementaryMetadataFilter'));
	}


	function handleSupplementaryMetadataFormExecute($hookName, $params)
	{
		error_log("########################################");
	}

	function supplementaryMetadataFilter($output, $templateMgr)
	{
		if (preg_match('/<div\s*class="section\s*formButtons/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset);
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('supplementaryMetaData.tpl'));
			$newOutput .= substr($output, $offset);
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'supplementaryMetadataFilter'));
		}
		return $output;
	}


}
