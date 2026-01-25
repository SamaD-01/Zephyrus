<?php

namespace App\Controller;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\DeviceType;

#[Route('/device')]
#[IsGranted('ROLE_USER')]
final class DeviceController extends AbstractController
{
    #[Route('/', name: 'device_index', methods: ['GET'])]
    public function index(DeviceRepository $deviceRepository): Response
    {
        $user = $this->getUser();
        $devices = $deviceRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('device/index.html.twig', [
            'devices' => $devices,
        ]);
    }

    #[Route('/new', name: 'device_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $device = new Device();
        $device->setUser($this->getUser());
        
        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($device);
            $entityManager->flush();

            $this->addFlash('success', 'Device registered successfully!');
            return $this->redirectToRoute('device_index');
        }

        return $this->render('device/new.html.twig', [
            'device' => $device,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'device_show', methods: ['GET'])]
    public function show(Device $device): Response
    {
        if ($device->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('device/show.html.twig', [
            'device' => $device,
        ]);
    }

    #[Route('/{id}/edit', name: 'device_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Device $device, EntityManagerInterface $entityManager): Response
    {
        if ($device->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Device updated successfully!');
            return $this->redirectToRoute('device_index');
        }

        return $this->render('device/edit.html.twig', [
            'device' => $device,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'device_delete', methods: ['POST'])]
    public function delete(Request $request, Device $device, EntityManagerInterface $entityManager): Response
    {
        if ($device->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$device->getId(), $request->request->get('_token'))) {
            $entityManager->remove($device);
            $entityManager->flush();
            
            $this->addFlash('success', 'Device deleted successfully!');
        }

        return $this->redirectToRoute('device_index');
    }

    #[Route('/{id}/toggle', name: 'device_toggle', methods: ['POST'])]
    public function toggle(Request $request, Device $device, EntityManagerInterface $entityManager): Response
    {
        if ($device->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('toggle'.$device->getId(), $request->request->get('_token'))) {
            $device->setIsActive(!$device->isActive());
            $entityManager->flush();
            
            $status = $device->isActive() ? 'activated' : 'deactivated';
            $this->addFlash('success', "Device {$status} successfully!");
        }

        return $this->redirectToRoute('device_index');
    }
    
}
