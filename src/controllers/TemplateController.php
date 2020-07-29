<?php

namespace codemonauts\shortener\controllers;

use codemonauts\shortener\elements\Template;
use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

class TemplateController extends Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePermission('shortener:templates');

        return true;
    }

    public function actionIndex()
    {
        return $this->renderTemplate('shortener/template/index');
    }

    public function actionEdit(int $templateId = null, Template $template = null)
    {
        if ($templateId !== null) {
            if ($template === null) {
                $template = Template::findOne(['id' => $templateId]);
                if (!$template) {
                    throw new NotFoundHttpException();
                }
            }
        } else {
            if ($template === null) {
                $template = new Template();
            }
        }

        $variables['template'] = $template;
        $variables['title'] = $template->id ? 'Edit template' : 'Create template';
        $variables['continueEditingUrl'] = 'shortener/template/{templateId}';
        $variables['isNew'] = !$template->id;

        $this->renderTemplate('shortener/template/_edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $templateId = $request->getBodyParam('templateId');

        if ($templateId) {
            $template = Template::findOne(['id' => $templateId]);
        } else {
            $template = new Template();
        }

        $template->title = $request->getBodyParam('title');
        $template->pattern = $request->getBodyParam('pattern');
        $template->description = $request->getBodyParam('description');
        $template->redirectCode = $request->getBodyParam('redirectCode');

        if (Craft::$app->elements->saveElement($template)) {
            Craft::$app->session->setName('Template saved.');

            return $this->redirectToPostedUrl($template);
        }

        Craft::$app->session->setError('Template not saved.');

        Craft::$app->urlManager->setRouteParams([
            'template' => $template,
        ]);

        return null;
    }
}
