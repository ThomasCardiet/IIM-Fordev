<?php

namespace App\Controller;

use App\Entity\FMessage;
use App\Entity\Friend;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyProfileController
    extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/profile";
    }

    public static function getControllerName(): string
    {
        return "My Profile";
    }

    public function __invoke(Request $request): Response
    {

        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $user_id = $request->getSession()->get("user_id");
        if($user_id === null) return $this->redirectToRoute("Home");

        $user = $user_repo->find($request->getSession()->get("user_id"));

        $friend_repo = $this->getDoctrine()->getRepository(Friend::class);
        $friends = $friend_repo->findBy(['user_id' => $user_id, 'friend_id' => $user_id]);

        $friends_requests = $friend_repo->findBy(['friend_id' => $user_id, 'accepted' => false]);

        return $this->render('Profile/profile.html.twig', [
            'user' => $user,
            'users' => $user_repo->findAll(),
            'friends' => $friends,
            'friends_requests' => $friends_requests,
        ]);
    }

    public function ajax(Request $request, $parameter): Response
    {
        $user_id = $request->getSession()->get('user_id');
        $friend_repo = $this->getDoctrine()->getRepository(Friend::class);
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $messages_repo = $this->getDoctrine()->getRepository(FMessage::class);

        $em = $this->getDoctrine()->getManager();

        if($parameter !== null) {
            switch ($parameter) {

                // FRIENDS SECTION
                case "addFriend":

                    $friend_id = (int)$request->request->get('friend_id');
                    $user_requests = $friend_repo->findBy(['user_id' => $user_id, 'friend_id' => $friend_id]);
                    $friend_requests = $friend_repo->findBy(['user_id' => $friend_id, 'friend_id' => $user_id]);


                    // IF REQUEST OF FRIEND ALREADY SEND
                    if(!empty($friend_requests)) {
                        foreach ($friend_requests as $friend_request) {
                            $friend_request->setAccepted(true);

                            $em->persist($friend_request);
                            $em->flush();
                        }
                        echo json_encode(['success' => true, 'msg' => 'Demande d\'ami acceptée.']);
                        break;
                    }

                    // IF REQUEST ALREADY SEND
                    if(!empty($user_requests)) {

                        echo json_encode(['success' => false, 'msg' => 'Vous avez déjà envoyé une demande d\'ami à cet utilisateur.']);
                        break;
                    }

                    $friend = new Friend();
                    $friend->setAccepted(false)
                        ->setUserId($user_id)
                        ->setFriendId($friend_id);

                    $em->persist($friend);
                    $em->flush();
                    echo json_encode(['success' => true, 'msg' => 'Demande d\'ami envoyée.']);
                    break;

                case "removeFriend":

                    $friend_id = $_POST["friend_id"];
                    $user_requests = $friend_repo->findBy(['user_id' => $user_id, 'friend_id' => $friend_id]);
                    $friend_requests = $friend_repo->findBy(['user_id' => $friend_id, 'friend_id' => $user_id]);
                    $friends_messages = array_merge(
                        $messages_repo->findBy(['owner' => $friend_id, 'friend' => $user_id]),
                        $messages_repo->findBy(['friend' => $friend_id, 'owner' => $user_id])
                    );

                    if(empty($user_requests) && empty($friend_requests)) {
                        echo json_encode(['success' => false, 'msg' => 'Vous n\'avez ou n\'êtes pas ami avec cet utilisateur.']);
                        break;
                    }

                    foreach ($user_requests as $user_request) {
                        $em->remove($user_request);
                    }

                    foreach ($friend_requests as $friend_request) {
                        $em->remove($friend_request);
                    }

                    foreach ($friends_messages as $friend_message) {
                        $em->remove($friend_message);
                    }

                    $em->flush();
                    echo json_encode(['success' => true, 'msg' => 'Ami supprimé avec succès.']);
                    break;

                case "getFriendRequests":
                    $RAW_QUERY = "SELECT user_id FROM friend WHERE friend_id = ? and accepted = ?";
                    $statement = $em->getConnection()->prepare($RAW_QUERY);
                    $statement->execute([$user_id, false]);
                    $results = $statement->fetchAll();
                    $requests = [];
                    if(empty($results)) {
                        echo json_encode(['exist_request' => false]);
                        break;
                    }
                    foreach ($results as $friend_request) {
                        $user = $em->getRepository(User::class)->find((int)$friend_request['user_id']);
                        $requests[] = ['id' => $user->getId(), 'email' => $user->getEmail(), 'username' => $user->getUsername()];
                    }
                    echo json_encode(['exist_request' => true, 'requests' => $requests]);
                    break;

                case "getNonRelationnedUsers":

                    $search_value = $_POST['search'];

                    $RAW_QUERY = "SELECT user.id FROM user
                                WHERE user.id NOT IN (SELECT friend.friend_id FROM friend WHERE friend.user_id = :user_id)
                                AND user.id NOT IN (SELECT friend.user_id FROM friend WHERE friend.friend_id = :user_id AND friend.accepted = :accepted)
                                AND user.id != :user_id";

                    $executes = ['user_id' => $user_id, 'accepted' => 1];
                    if(!empty($search_value)) {
                        $executes['like'] = $search_value;
                        $RAW_QUERY.=" AND user.username LIKE '%".$search_value."%'";
                    }

                    $statement = $em->getConnection()->prepare($RAW_QUERY);
                    $statement->execute($executes);
                    $results = $statement->fetchAll();

                    $requests = [];
                    if(empty($results)) {
                        echo json_encode(['exist_users' => false]);
                        break;
                    }
                    foreach ($results as $id) {
                        $user = $em->getRepository(User::class)->find($id);
                        $requests[] = ['id' => $user->getId(), 'email' => $user->getEmail(), 'username' => $user->getUsername()];
                    }
                    echo json_encode(['exist_users' => true, 'users' => $requests]);
                    break;

                case "getCurrentFriends":

                    $search_value = $_POST['search'];

                    $friends = array_merge(
                      $friend_repo->findBy(['user_id' => $user_id, 'accepted' => true]),
                      $friend_repo->findBy(['friend_id' => $user_id, 'accepted' => true])
                    );

                    // IF SEARCH VALUE
                    if(!empty($search_value)) {
                        $friends = array_merge(
                            $friend_repo->search(['friend.user_id' => $user_id, 'friend.accepted' => true, 'user.id' => 'friend.friend_id'], $search_value),
                            $friend_repo->search(['friend.friend_id' => $user_id, 'friend.accepted' => true, 'user.id' => 'friend.user_id'], $search_value)
                        );
                    }

                    $users = [];
                    foreach ($friends as $friend) {
                        $id = null;
                        if($friend->getUserId() === $user_id) {
                            $id = $friend->getFriendId();
                        }else $id = $friend->getUserId();

                        $user = $user_repo->find($id);
                        $user_infos = ['id' => $id, 'email' => $user->getEmail(), 'username' => $user->getUsername()];
                        if(in_array($user_infos, $users)) continue;
                        $users[] = $user_infos;
                    }

                    if(empty($users)) {
                        echo json_encode(['exist_friends' => false]);
                        break;
                    }
                    echo json_encode(['exist_friends' => true, 'users' => $users, 'search' => !empty($search_value)]);
                    break;

                case "getFriendMessages":

                    $friend_id = $_POST['friend_id'];
                    $friend = $user_repo->find($friend_id);

                    echo json_encode(['username' => $friend->getUsername(), 'messages' => $this->getFriendMessages($em, $user_repo, $user_id, $friend_id)]);

                    break;

                case "sendFriendMessage":

                    $friend_id = $_POST['friend_id'];
                    $value = $_POST['value'];

                    $fMessage = new FMessage();
                    $fMessage->setCreateAt(new \DateTime())
                        ->setContent($value)
                        ->setOwner($user_repo->find($user_id))
                        ->setFriend($user_repo->find($friend_id));

                    $em->persist($fMessage);
                    $em->flush();

                    echo json_encode(['success' => true]);
                    break;

                case "getLastConversations":

                    $RAW_QUERY = "SELECT * FROM fmessage
                                WHERE fmessage.owner_id = ? OR fmessage.friend_id = ?
                                ORDER BY fmessage.create_at";

                    $statement = $em->getConnection()->prepare($RAW_QUERY);
                    $statement->execute([$user_id, $user_id]);
                    $results = $statement->fetchAll();

                    $last_users_id = [];

                    foreach ($results as $result) {
                        $id = null;
                        if($result['owner_id'] === (String)$user_id) $id = $result['friend_id'];
                        else $id = $result['owner_id'];

                        if($id === null ) {
                            echo json_encode(['success' => false]);
                            return false;
                        }
                        if(in_array($id, $last_users_id)) continue;

                        $last_users_id[] = $id;

                        if(count($last_users_id) >= 5) break;
                    }

                    $values = [];
                    foreach ($last_users_id as $id) {

                        $last_messages = $this->getFriendMessages($em, $user_repo, $user_id, $id);
                        $user = $user_repo->find($id);
                        $values[] = ["user" => ['id' => $user->getId(), 'email' => $user->getEmail()], "last_msg" => $last_messages[count($last_messages)-1]];

                    }

                    echo json_encode(['success' => true, 'values' => $values]);

                    break;

                // MY PROFILE SECTION
                case "changeUsername":

                    $user = $user_repo->find($user_id);
                    $input = $_POST['input'];

                    $user->setUsername($input);
                    $em->persist($user);
                    $em->flush();

                    break;
            }
        }

        return $this->render('ajax.html.twig');
    }

    function getFriendMessages($em, $user_repo, $user_id, $friend_id) {

        $friend = $user_repo->find($friend_id);

        $RAW_QUERY = "SELECT * FROM fmessage
                                WHERE fmessage.owner_id = ? AND fmessage.friend_id = ?
                                OR fmessage.friend_id = ? AND fmessage.owner_id = ?
                                ORDER BY fmessage.create_at";

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute([$user_id, $friend_id, $user_id, $friend_id]);
        $results = $statement->fetchAll();

        foreach ($results as $key => $result) {
            $results[$key]["owner"] = $user_repo->find($result["owner_id"]);
        }

        return $results;

    }
}