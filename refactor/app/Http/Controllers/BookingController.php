<?php

namespace DTApi\Http\Controllers;

use App\Enums\Role;
use DTApi\Http\Controllers\Controller;
use DTApi\Http\Controllers\JobResource;
use DTApi\Http\Controllers\StoreJobRequest;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use DTApi\Models\Job;
use DTApi\Repository\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function DTApi\Http\Controllers\array_except;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);

        } else if (in_array(
            $request->__authenticatedUser->user_type,
            [Role::ADMIN_ROLE_ID, Role::SUPERADMIN_ROLE_ID]
        )) {
            $response = $this->repository
                ->getAll($request->all(), $request->__authenticatedUser)
                ->paginate(15);

        } else {
            $response = ['status' => 'fail', 'message', __('error.something_went_wrong')];
        }
        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return response(
            $this->repository->with('translatorJobRel.user')->find($id)
        );
    }

    /**
     * @param StoreJobRequest $request
     * @return mixed
     */
    public function store(StoreJobRequest $request)
    {
        return $this->repository->store(
            $request->__authenticatedUser,
            $request->validated() // more secure than $request->all()
        );
    }

    /**
     * @param $id
     * @param UpdateJobRequest $request
     * @return mixed
     */
    public function update($id, UpdateJobRequest $request)
    {
        return response(
            $this->repository->updateJob(
                $id,
                $request->except(['_token', 'submit']),
                $request->__authenticatedUser)
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        return response(
            $this->repository->storeJobEmail($request->all())
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'],
            $request->input()
        );

        return response(
            $this->repository->getUsersJobsHistory($request->input('user_id'), $request)
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        return response(
            $this->repository->acceptJobWithId(
                $request->input('job_id'), $request->__authenticatedUser
            )
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        return response(
            $this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser)
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        return response(
            $this->repository->endJob($request->all())
        );

    }

    public function customerNotCall(Request $request)
    {
        return response(
            $this->repository->customerNotCall(
                $request->all()
            )
        );

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        return $this->repository->getPotentialJobs(
            $request->__authenticatedUser
        );
    }

    public function distanceFeed(Request $request)
    {
        $request->validate([
            'admincomment' => 'required_if:flagged:true'
        ], '');

        $data = $request->all();

        // if columns having yes/no values had boolean column, than
        // model's cast option could be used that will every time convert 0,1 to yes/no
        // and this way there would not any need in whole app to having following checks in whole app
        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $jobid = $data['jobid'] ?? '';
        $session = $data['session_time'] ?? '';
        $flagged = $data['flagged'] ?? 'no';
        $manually_handled = $data['manually_handled'] ?? 'no';
        $by_admin = $data['by_admin'] ?? 'no';
        $admincomment = $data['admincomment'] ?? '';


        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)
                ->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)
                ->update(array(
                        'admin_comments' => $admincomment,
                        'flagged' => $flagged,
                        'session_time' => $session,
                        'manually_handled' => $manually_handled,
                        'by_admin' => $by_admin)
                );
        }
        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        return response(
            $this->repository->reopen($request->all())
        );
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->input('jobid'));
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $job = $this->repository->find($request->input('jobid'));

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response(['success' => __('errors.something_went_wrong')]);
        }
    }

}
