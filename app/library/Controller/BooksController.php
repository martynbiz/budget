<?php
namespace App\Controller;

use App\Validator;

class BooksController extends BaseController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = (int)$request->getQueryParam('limit', 20);
        $start = ($page-1) * $limit;

        // get paginated rows
        $books = $currentUser->books()
            ->with('user')
            ->skip($start)
            ->take($limit)
            ->get();

        return $this->render('books/index', [
            'books' => $books,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();
        $container = $this->getContainer();

        return $this->render('books/create', [
            'params' => $params,
        ]);
    }

    public function post($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // description
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // if valid, create book
        if ($validator->isValid()) {

            if ($book = $currentUser->books()->create($params)) {

                // redirect
                return $response->withRedirect('/');

            } else {
                $errors = $book->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();
        $book = $this->getCurrentUser()->books()->findOrFail((int)$args['book_id']);

        // if errors found from post, this will contain data
        $params = array_merge($book->toArray(), $request->getParams());

        return $this->render('books/edit', [
            'params' => $params,
            'book' => $book,
            'currencies' => $currencies,
        ]);
    }

    public function update($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // description
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // if valid, create book
        if ($validator->isValid()) {

            $book = $currentUser->books()->findOrFail((int)$args['book_id']);

            if ($book->update($params)) {

                // redirect
                return $response->withRedirect('/books');

            } else {
                $errors = $book->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    public function delete($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        $book = $currentUser->books()->findOrFail((int)$args['book_id']);
        $bookId = $book->id;

        if ($book->delete()) {

            // remove all transactions
            $transactions = $book->transactions()
                ->where('book_id', $bookId)
                ->delete();

            // redirect
            return $response->withRedirect('/books');

        } else {
            $errors = $book->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }
}
