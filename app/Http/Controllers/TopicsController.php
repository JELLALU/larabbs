<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Auth;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(Request $request, Topic $topic)
    {
        $private_topics = $topic->select('id')->where('category_id', '=', 5)->whereIn('user_id', [1, 2])->get()->toArray();
        $private_ids = array_column($private_topics, 'id');

        $topics = $topic->whereNotIn('id', $private_ids)->withOrder($request->order)->paginate(20);
        return view('topics.index', compact('topics'));
    }

    public function show(Request $request, Topic $topic)
    {
        if ($topic->category_id == 5 && in_array($topic->user_id, [1, 2]) && !in_array(Auth::id(), [1, 2])) {
            return redirect()->route('topics.index')->with('message', '无权限查看此文章！');
        }

        // URL 矫正
        if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
	    $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
        $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();

		return redirect()->to($topic->link())->with('message', '成功创建话题！');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);

        if ($topic->user_id == 2 && Auth::id() == 2) {
            return redirect()->route('topics.index')->with('message', '此文章无法编辑！');
        }

        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);

        if (Auth::id() != 2) {
		    $topic->update($request->all());
            return redirect()->to($topic->link())->with('message', '更新成功！');
        }

		return redirect()->to($topic->link())->with('message', '更新失败！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);

		if (Auth::id() != 2) {
            $topic->delete();
            return redirect()->route('topics.index')->with('message', '删除成功！');
        }

        return redirect()->route('topics.index')->with('message', '删除失败！');
	}

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认是失败的
        $data = [
            'success'   => false,
            'msg'       => '上传失败!',
            'file_path' => ''
        ];
        // 判断是否有上传文件，并赋值给 $file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
            // 图片保存成功的话
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg']       = "上传成功!";
                $data['success']   = true;
            }
        }
        return $data;
    }
}