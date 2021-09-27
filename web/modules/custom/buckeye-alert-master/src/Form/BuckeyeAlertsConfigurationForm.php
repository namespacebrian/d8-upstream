<?php

    namespace Drupal\buckeye_alert\Form;

    use Drupal\Component\Datetime\DateTimePlus;
    use Drupal\Core\Datetime\DrupalDateTime;
    use Drupal\Core\Form\ConfigFormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Component\Utility\Xss;

    class BuckeyeAlertsConfigurationForm extends ConfigFormBase {

      protected function getEditableConfigNames() {
        return ['buckeye_alert.buckeye_alert_config'];
      }

      public function getFormId() {
        return 'buckeye_alert_configuration_form';
      }

      public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('buckeye_alert.buckeye_alert_config');

        //get stored configuration values
        $buckeye_alert_url = $config->get('buckeye_alert_url');
        $buckeye_alert_test = $config->get('buckeye_alert_test');
        $buckeye_alert_class = $config->get('buckeye_alert_class');
        $buckeye_alert_animate = $config->get('buckeye_alert_animate');
        $buckeye_alert_addl = $config->get('buckeye_alert_addl');
        $buckeye_alert_addl_date = $config->get('buckeye_alert_addl_date');
        $buckeye_alert_addl_expire = $config->get('buckeye_alert_addl_expire');

        $form['intro'] = array(
          '#markup' => t('<p>Controls the display of Buckeye Alert messages.</p>'),
        );

        $form['buckeye_alert_url'] = array(
          '#type' => 'textfield',
          '#title' => t('Alert feed URL'),
          '#default_value' => isset($buckeye_alert_url) ? $buckeye_alert_url : '//www.osu.edu/feeds/emergency-alert.rss',
          '#description' => t('Default: //www.osu.edu/feeds/emergency-alert.rss'),
          '#required' => TRUE,
        );

        $form['buckeye_alert_test'] = array(
          '#type' => 'checkbox',
          '#title' => t('Use the test feed for Buckeye Alerts'),
          '#default_value' => isset($buckeye_alert_test) ? $buckeye_alert_test : FALSE,
        );

        $form['buckeye_alert_class'] = array(
          '#type' => 'textfield',
          '#title' => t('Message container class'),
          '#description' => t('A space separated list of classes for the inner alert container'),
          '#default_value' => isset($buckeye_alert_class) ? $buckeye_alert_class : 'l-constrained',
        );

        $form['buckeye_alert_animate'] = array(
          '#type' => 'checkbox',
          '#title' => t('Animate alert messages'),
          '#default_value' => isset($buckeye_alert_animate) ? $buckeye_alert_animate : TRUE,
        );

        $form['additional'] = array(
          '#type' => 'fieldset',
          '#title' => t('Additional messages'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
        );

        $form['additional']['intro'] = array(
          '#markup' => t('<p>Additional messages to display. To be used ONLY during a weather-related alert.</p>'),
        );

        $form['additional']['buckeye_alert_addl'] = array(
          '#type' => 'textarea',
          '#title' => t('Messages'),
          '#default_value' => isset($buckeye_alert_addl) ? $buckeye_alert_addl : '',
          '#description' => t('Can contain: p a ul ol li'),
          '#required' => FALSE,
        );

        $form['additional']['buckeye_alert_addl_date'] = array(
          '#type' => 'datetime',
          '#title' => t('Publish on'),
          '#date_increment' => 1,
          '#default_value' => isset($buckeye_alert_addl_date) ? new DrupalDateTime($buckeye_alert_addl_date) : null, //convert config string to date object
          '#required' => FALSE,
        );

        $form['additional']['buckeye_alert_addl_expire'] = array(
          '#type' => 'textfield',
          '#attributes' => array(
            ' type' => 'number',
          ),
          '#title' => t('Expires'),
          '#default_value' => isset($buckeye_alert_addl_expire) ? $buckeye_alert_addl_expire : 24,
          '#description' => t('Number of hours after the publish time that the message expires.'),
          '#required' => FALSE,
        );

        return parent::buildForm($form, $form_state);
      }

      public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('buckeye_alert.buckeye_alert_config');

        $config->set('buckeye_alert_url', $form_state->getValue('buckeye_alert_url'))->save();
        $config->set('buckeye_alert_test', $form_state->getValue('buckeye_alert_test'))->save();
        $config->set('buckeye_alert_class', $form_state->getValue('buckeye_alert_class'))->save();
        $config->set('buckeye_alert_animate', $form_state->getValue('buckeye_alert_animate'))->save();
        $config->set('buckeye_alert_addl', $form_state->getValue('buckeye_alert_addl'))->save();

        //convert date object to string for storage in config
        if($form_state->getValue('buckeye_alert_addl_date') !== null){
          $config->set('buckeye_alert_addl_date', $form_state->getValue('buckeye_alert_addl_date')->format('Y-m-d H:i:s'))->save();
        } else {
          $config->set('buckeye_alert_addl_date', $form_state->getValue('buckeye_alert_addl_date'))->save();
        }

        $config->set('buckeye_alert_addl_expire', $form_state->getValue('buckeye_alert_addl_expire'))->save();

        parent::submitForm($form, $form_state);
      }

      public function validateForm(array &$form, FormStateInterface $form_state) {

        //makes sure additional message date is in the future if it is set
        //if($form_state->getValue('buckeye_alert_addl_date') !== null && $form_state->getValue('buckeye_alert_addl_date') < date('Y-m-d H:i:s') ){
        //  $form_state->setErrorByName('buckeye_alert_addl_date', $this->t('Publish date cannot be in the past'));
        //}

        //makes sure the additional message length is a positive integer
        if($form_state->getValue('buckeye_alert_addl_expire') <= 0){
          $form_state->setErrorByName('buckeye_alert_addl_expire', $this->t('Invalid expiration time'));
        }

        //disallow restricted html characters
        $allowed = array('p', 'a', 'ul', 'ol', 'li');
        $message = Xss::filter($form_state->getValue('buckeye_alert_addl'), $allowed);
        //ensure p tag
        if(!empty($message) && stripos($message, '<p') === false) {
          $message = '<p>' . $message . '</p>';
          $form_state->setValue('buckeye_alert_addl', $message);
        }

        parent::validateForm($form, $form_state);
      }
    }
