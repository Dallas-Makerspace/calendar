<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Honoraria Controller
 *
 * @property \App\Model\Table\HonorariaTable $Honoraria
 */
class HonorariaController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Events', 'Committees']
        ];
        $honoraria = $this->paginate($this->Honoraria);

        $this->set(compact('honoraria'));
        $this->set('_serialize', ['honoraria']);
    }

    /**
     * View method
     *
     * @param string|null $id Honorarium id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $honorarium = $this->Honoraria->get($id, [
            'contain' => ['Events', 'Committees']
        ]);

        $this->set('honorarium', $honorarium);
        $this->set('_serialize', ['honorarium']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $honorarium = $this->Honoraria->newEntity();
        if ($this->request->is('post')) {
            $honorarium = $this->Honoraria->patchEntity($honorarium, $this->request->data);
            if ($this->Honoraria->save($honorarium)) {
                $this->Flash->success(__('The honorarium has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The honorarium could not be saved. Please, try again.'));
            }
        }
        $events = $this->Honoraria->Events->find('list', ['limit' => 200]);
        $committees = $this->Honoraria->Committees->find('list', ['limit' => 200]);
        $this->set(compact('honorarium', 'events', 'committees'));
        $this->set('_serialize', ['honorarium']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Honorarium id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $honorarium = $this->Honoraria->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $honorarium = $this->Honoraria->patchEntity($honorarium, $this->request->data);
            if ($this->Honoraria->save($honorarium)) {
                $this->Flash->success(__('The honorarium has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The honorarium could not be saved. Please, try again.'));
            }
        }
        $events = $this->Honoraria->Events->find('list', ['limit' => 200]);
        $committees = $this->Honoraria->Committees->find('list', ['limit' => 200]);
        $this->set(compact('honorarium', 'events', 'committees'));
        $this->set('_serialize', ['honorarium']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Honorarium id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $honorarium = $this->Honoraria->get($id);
        if ($this->Honoraria->delete($honorarium)) {
            $this->Flash->success(__('The honorarium has been deleted.'));
        } else {
            $this->Flash->error(__('The honorarium could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
