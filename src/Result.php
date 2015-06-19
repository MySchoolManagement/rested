<?php
namespace Rested;

use Symfony\Component\Form\FormInterface;

class Result
{

    /**
     * Data pertaining to the result of this endpoint.
     *
     * @var array|object
     */
    public $data;

    /**
     * List of high-level messages (warnings, errors) about the result of this endpoint.
     *
     * @var array
     */
    public $messages = [];

    /**
     * Adds form data to the result.
     */
    public function addForm(FormInterface $form)
    {
        $this->validation = [
            'is_valid' => $form->isValid(),
            'errors'   => []
        ];

        $this->getErrorMessages($form);
    }

    public function addMessage($type, $message)
    {
        $this->messages[] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    private function getErrorMessages(FormInterface $form)
    {
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if ($child->isValid() == false) {
                    $this->getErrorMessages($child);
                }
            }
        } else {
            foreach ($form->getErrors() as $error) {
                $this->validation['errors'][$form->getName()] = $error->getMessage();
            }
        }
    }
}